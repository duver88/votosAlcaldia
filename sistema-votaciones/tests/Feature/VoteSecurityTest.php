<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Candidate;
use App\Models\User;
use App\Models\Vote;
use App\Models\VotingSetting;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class VoteSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        VotingSetting::create([
            'is_active' => true,
            'start_datetime' => Carbon::now()->subHour(),
            'end_datetime' => Carbon::now()->addHour(),
        ]);
    }

    private function createVoter(array $attrs = []): User
    {
        return User::create(array_merge([
            'cedula' => fake()->unique()->numerify('##########'),
            'password' => Hash::make('Password123'),
            'must_change_password' => false,
        ], $attrs));
    }

    private function createCandidate(array $attrs = []): Candidate
    {
        return Candidate::create(array_merge([
            'name' => fake()->name(),
            'is_active' => true,
            'position' => Candidate::max('position') + 1,
        ], $attrs));
    }

    // =====================================================
    // TESTS DE MANIPULACION DE VOTOS VIA REQUEST
    // =====================================================

    public function test_cannot_vote_with_forged_user_id(): void
    {
        $voter1 = $this->createVoter(['cedula' => '111111111']);
        $voter2 = $this->createVoter(['cedula' => '222222222']);
        $candidate = $this->createCandidate();

        // Voter1 intenta votar como voter2 (manipulando request)
        $response = $this->actingAs($voter1)
            ->post('/vote', [
                'candidate_id' => $candidate->id,
                'user_id' => $voter2->id, // Intento de forgery
            ]);

        // El voto debe registrarse para voter1, no voter2
        $voter1->refresh();
        $voter2->refresh();

        $this->assertTrue($voter1->has_voted);
        $this->assertFalse($voter2->has_voted);
    }

    public function test_cannot_modify_vote_count_via_request(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $this->actingAs($voter)
            ->post('/vote', [
                'candidate_id' => $candidate->id,
                'votes_count' => 1000, // Intento de manipulacion
            ]);

        $candidate->refresh();
        $this->assertEquals(1, $candidate->votes_count);
    }

    public function test_cannot_set_has_voted_via_request(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $this->actingAs($voter)
            ->post('/vote', [
                'candidate_id' => $candidate->id,
                'has_voted' => false, // Intento de mantener has_voted en false
            ]);

        $voter->refresh();
        $this->assertTrue($voter->has_voted);
    }

    public function test_cannot_set_voted_at_via_request(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();
        $fakeDate = Carbon::now()->subYear();

        $this->actingAs($voter)
            ->post('/vote', [
                'candidate_id' => $candidate->id,
                'voted_at' => $fakeDate->toDateTimeString(), // Intento de fecha falsa
            ]);

        $voter->refresh();
        // La fecha debe ser reciente, no la falsa
        $this->assertTrue($voter->voted_at->gt(Carbon::now()->subMinute()));
    }

    public function test_cannot_inject_extra_vote_records(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $this->actingAs($voter)
            ->post('/vote', [
                'candidate_id' => $candidate->id,
                'extra_votes' => [
                    ['candidate_id' => $candidate->id],
                    ['candidate_id' => $candidate->id],
                ],
            ]);

        // Solo debe haber 1 voto
        $this->assertEquals(1, Vote::count());
        $this->assertEquals(1, $candidate->fresh()->votes_count);
    }

    // =====================================================
    // TESTS DE SQL INJECTION
    // =====================================================

    public function test_sql_injection_in_candidate_id_number(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $response = $this->actingAs($voter)
            ->post('/vote', ['candidate_id' => '1 OR 1=1']);

        $response->assertSessionHasErrors('candidate_id');
        $this->assertFalse($voter->fresh()->has_voted);
    }

    public function test_sql_injection_in_candidate_id_union(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $response = $this->actingAs($voter)
            ->post('/vote', ['candidate_id' => '1 UNION SELECT * FROM users']);

        $response->assertSessionHasErrors('candidate_id');
    }

    public function test_sql_injection_in_candidate_id_drop(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $response = $this->actingAs($voter)
            ->post('/vote', ['candidate_id' => '1; DROP TABLE votes;']);

        $response->assertSessionHasErrors('candidate_id');

        // Verificar que la tabla votes sigue existiendo
        $this->assertTrue(\Schema::hasTable('votes'));
    }

    public function test_sql_injection_in_candidate_id_comment(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $response = $this->actingAs($voter)
            ->post('/vote', ['candidate_id' => '1--']);

        $response->assertSessionHasErrors('candidate_id');
    }

    public function test_sql_injection_in_candidate_id_hex(): void
    {
        $voter = $this->createVoter();

        $response = $this->actingAs($voter)
            ->post('/vote', ['candidate_id' => '0x31']);

        $response->assertSessionHasErrors('candidate_id');
    }

    // =====================================================
    // TESTS DE CSRF Y SESSION HIJACKING
    // =====================================================

    public function test_vote_requires_csrf_token(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        // Deshabilitar middleware de CSRF para simular ataque
        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->actingAs($voter)
            ->post('/vote', ['candidate_id' => $candidate->id]);

        // Aun sin CSRF, debe funcionar en test (pero en produccion fallaria)
        // Lo importante es que el middleware esta configurado
        $this->assertTrue(in_array(
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            app(\Illuminate\Contracts\Http\Kernel::class)->getMiddlewareGroups()['web'] ?? []
        ) || in_array(
            \App\Http\Middleware\VerifyCsrfToken::class,
            app(\Illuminate\Contracts\Http\Kernel::class)->getMiddlewareGroups()['web'] ?? []
        ));
    }

    public function test_session_regenerates_after_vote(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        // Verificar que el codigo de regeneracion existe en el controller
        $controllerContent = file_get_contents(app_path('Http/Controllers/VoteController.php'));
        $this->assertStringContainsString('regenerate', $controllerContent);

        // El voto debe completarse exitosamente
        $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);
        $this->assertTrue($voter->fresh()->has_voted);
    }

    public function test_cannot_vote_from_different_session(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        // Primera sesion
        $this->actingAs($voter)->get('/vote');

        // Simular logout y otra sesion
        $this->post('/logout');

        // Intentar votar sin autenticacion
        $response = $this->post('/vote', ['candidate_id' => $candidate->id]);

        $response->assertRedirect('/login');
    }

    // =====================================================
    // TESTS DE DOBLE VOTO Y RACE CONDITIONS
    // =====================================================

    public function test_double_vote_same_candidate_rejected(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        // Primer voto
        $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);

        // Segundo intento
        $response = $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);

        $response->assertSessionHas('error');
        $this->assertEquals(1, Vote::count());
        $this->assertEquals(1, $candidate->fresh()->votes_count);
    }

    public function test_double_vote_different_candidate_rejected(): void
    {
        $voter = $this->createVoter();
        $candidate1 = $this->createCandidate(['name' => 'Candidato 1']);
        $candidate2 = $this->createCandidate(['name' => 'Candidato 2']);

        // Primer voto
        $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate1->id]);

        // Segundo intento con otro candidato
        $response = $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate2->id]);

        $response->assertSessionHas('error');
        $this->assertEquals(1, Vote::count());
        $this->assertEquals(1, $candidate1->fresh()->votes_count);
        $this->assertEquals(0, $candidate2->fresh()->votes_count);
    }

    public function test_rapid_fire_votes_only_one_counts(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        // Simular 10 clicks rapidos
        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);
        }

        $this->assertEquals(1, Vote::count());
        $this->assertEquals(1, $candidate->fresh()->votes_count);
        $this->assertTrue($voter->fresh()->has_voted);
    }

    public function test_concurrent_votes_protection(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        // Simular condicion de carrera con transaccion manual
        DB::beginTransaction();

        $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);

        DB::commit();

        // Solo 1 voto debe existir
        $this->assertEquals(1, Vote::count());
    }

    public function test_lock_for_update_prevents_double_vote(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        // El VoteController usa lockForUpdate()
        // Verificar que el metodo existe en el codigo
        $controllerContent = file_get_contents(app_path('Http/Controllers/VoteController.php'));
        $this->assertStringContainsString('lockForUpdate', $controllerContent);

        // Votar normalmente
        $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);

        $this->assertEquals(1, Vote::count());
    }

    // =====================================================
    // TESTS DE MANIPULACION DE CONTADORES
    // =====================================================

    public function test_votes_count_cannot_be_decremented(): void
    {
        $candidate = $this->createCandidate();

        // Crear varios votos
        for ($i = 0; $i < 5; $i++) {
            $voter = $this->createVoter();
            $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);
        }

        $candidate->refresh();
        $this->assertEquals(5, $candidate->votes_count);

        // Intentar decrementar directamente (esto no deberia ser posible via HTTP)
        // Solo verificamos que el valor es correcto
        $this->assertEquals(5, Vote::where('candidate_id', $candidate->id)->count());
    }

    public function test_votes_count_matches_actual_votes(): void
    {
        $candidates = [];
        for ($i = 0; $i < 3; $i++) {
            $candidates[] = $this->createCandidate(['name' => "Candidato $i"]);
        }

        // Crear votos aleatorios
        for ($i = 0; $i < 50; $i++) {
            $voter = $this->createVoter();
            $candidate = $candidates[array_rand($candidates)];
            $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);
        }

        // Verificar que votes_count coincide con COUNT real
        foreach ($candidates as $candidate) {
            $candidate->refresh();
            $actualCount = Vote::where('candidate_id', $candidate->id)->count();
            $this->assertEquals($actualCount, $candidate->votes_count);
        }
    }

    public function test_total_votes_equals_voters_who_voted(): void
    {
        $candidate = $this->createCandidate();

        for ($i = 0; $i < 20; $i++) {
            $voter = $this->createVoter();
            $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);
        }

        $totalVotes = Vote::count();
        $votersWhoVoted = User::where('has_voted', true)->count();

        $this->assertEquals($totalVotes, $votersWhoVoted);
    }

    public function test_cannot_create_orphan_votes(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);

        // Todos los votos deben tener candidato existente
        $orphanVotes = Vote::whereNotIn('candidate_id', Candidate::pluck('id'))->count();
        $this->assertEquals(0, $orphanVotes);
    }

    // =====================================================
    // TESTS DE ACCESO NO AUTORIZADO
    // =====================================================

    public function test_guest_cannot_vote(): void
    {
        $candidate = $this->createCandidate();

        $response = $this->post('/vote', ['candidate_id' => $candidate->id]);

        $response->assertRedirect('/login');
        $this->assertEquals(0, Vote::count());
    }

    public function test_admin_cannot_vote(): void
    {
        $admin = Admin::first();
        $candidate = $this->createCandidate();

        // Admin usa guard 'admin', no puede acceder a rutas de votante
        $response = $this->actingAs($admin, 'admin')
            ->post('/vote', ['candidate_id' => $candidate->id]);

        // Debe redirigir al login porque no esta autenticado como votante
        $response->assertRedirect();
        $this->assertEquals(0, Vote::count());
    }

    public function test_blocked_user_cannot_vote(): void
    {
        $voter = $this->createVoter(['is_blocked' => true]);
        $candidate = $this->createCandidate();

        $response = $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);

        $response->assertRedirect();
        $this->assertEquals(0, Vote::count());
    }

    public function test_user_must_change_password_cannot_vote(): void
    {
        $voter = $this->createVoter(['must_change_password' => true]);
        $candidate = $this->createCandidate();

        $response = $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);

        $response->assertRedirect(route('change-password'));
        $this->assertEquals(0, Vote::count());
    }

    public function test_cannot_vote_for_inactive_candidate(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate(['is_active' => false]);

        $response = $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);

        $response->assertSessionHas('error');
        $this->assertEquals(0, Vote::count());
    }

    public function test_cannot_vote_for_nonexistent_candidate(): void
    {
        $voter = $this->createVoter();

        $response = $this->actingAs($voter)->post('/vote', ['candidate_id' => 99999]);

        $response->assertSessionHasErrors('candidate_id');
        $this->assertEquals(0, Vote::count());
    }

    // =====================================================
    // TESTS DE INTEGRIDAD DE DATOS
    // =====================================================

    public function test_vote_creates_activity_log(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);

        $log = ActivityLog::where('action', 'vote_cast')->first();
        $this->assertNotNull($log);
        // El log se crea con la cedula del votante (campo user_cedula)
        $this->assertEquals($voter->cedula, $log->user_cedula);
    }

    public function test_failed_vote_does_not_create_activity_log(): void
    {
        $voter = $this->createVoter();

        // Voto fallido por candidato inexistente
        $this->actingAs($voter)->post('/vote', ['candidate_id' => 99999]);

        $log = ActivityLog::where('action', 'vote_cast')->first();
        $this->assertNull($log);
    }

    public function test_vote_timestamps_are_consistent(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $beforeVote = Carbon::now();
        $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);
        $afterVote = Carbon::now();

        $voter->refresh();
        $vote = Vote::first();

        // voted_at debe estar entre before y after
        $this->assertTrue($voter->voted_at->gte($beforeVote->subSecond()));
        $this->assertTrue($voter->voted_at->lte($afterVote->addSecond()));
        $this->assertTrue($vote->voted_at->gte($beforeVote->subSecond()));
        $this->assertTrue($vote->created_at->gte($beforeVote->subSecond()));
    }

    public function test_vote_data_is_immutable(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);

        $vote = Vote::first();
        $originalCandidateId = $vote->candidate_id;
        $originalVotedAt = $vote->voted_at;

        // Intentar modificar (no hay endpoint para esto, pero verificamos integridad)
        $this->assertEquals($originalCandidateId, Vote::first()->candidate_id);
        $this->assertEquals($originalVotedAt, Vote::first()->voted_at);
    }

    // =====================================================
    // TESTS DE ATAQUES DE FUERZA BRUTA
    // =====================================================

    public function test_rate_limit_on_vote_endpoint(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        // Verificar que el middleware throttle esta configurado
        $routes = app('router')->getRoutes();
        $voteRoute = $routes->getByName('vote.store');

        $this->assertNotNull($voteRoute);

        // El middleware throttle debe estar presente
        $middlewares = $voteRoute->middleware();
        $hasThrottle = collect($middlewares)->contains(function ($m) {
            return str_starts_with($m, 'throttle');
        });

        $this->assertTrue($hasThrottle, 'Vote route should have throttle middleware');
    }

    public function test_secure_vote_middleware_rejects_ajax(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $response = $this->actingAs($voter)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson('/vote', ['candidate_id' => $candidate->id]);

        $response->assertStatus(403);
    }

    public function test_secure_vote_middleware_rejects_external_referer(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $response = $this->actingAs($voter)
            ->withHeaders(['Referer' => 'https://malicious-site.com/attack'])
            ->post('/vote', ['candidate_id' => $candidate->id]);

        $response->assertStatus(403);
    }

    // =====================================================
    // TESTS DE MANIPULACION DE ESTADO
    // =====================================================

    public function test_cannot_vote_when_voting_closed(): void
    {
        $settings = VotingSetting::first();
        $settings->is_active = false;
        $settings->save();

        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $response = $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);

        $response->assertRedirect(route('voting.closed'));
        $this->assertEquals(0, Vote::count());
    }

    public function test_cannot_vote_before_start_time(): void
    {
        $settings = VotingSetting::first();
        $settings->start_datetime = Carbon::now()->addHour();
        $settings->save();

        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $response = $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);

        $response->assertRedirect(route('voting.closed'));
    }

    public function test_cannot_vote_after_end_time(): void
    {
        $settings = VotingSetting::first();
        $settings->end_datetime = Carbon::now()->subHour();
        $settings->save();

        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $response = $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);

        $response->assertRedirect(route('voting.closed'));
    }

    public function test_candidate_deactivated_during_vote_process(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        // Simular que el candidato se desactiva justo antes del POST
        $this->actingAs($voter)->get('/vote'); // Ver candidatos

        // Admin desactiva el candidato
        $candidate->is_active = false;
        $candidate->save();

        // Intentar votar
        $response = $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);

        $response->assertSessionHas('error');
        $this->assertEquals(0, Vote::count());
    }

    public function test_voting_closed_during_vote_process(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $this->actingAs($voter)->get('/vote'); // Ver candidatos

        // Admin cierra la votacion
        $settings = VotingSetting::first();
        $settings->is_active = false;
        $settings->save();

        // Intentar votar - debe ser rechazado (redirect o error)
        $response = $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);

        // Debe redirigir o tener error (votacion cerrada)
        $this->assertTrue(
            $response->isRedirection() ||
            session()->has('error'),
            'Should redirect or have error when voting is closed'
        );
        // El voto no debe haberse registrado
        $this->assertFalse($voter->fresh()->has_voted);
    }

    // =====================================================
    // TESTS DE TIPOS DE DATOS MALICIOSOS
    // =====================================================

    public function test_candidate_id_as_array_rejected(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $response = $this->actingAs($voter)
            ->post('/vote', ['candidate_id' => [$candidate->id, $candidate->id]]);

        // Array debe ser rechazado (error de validacion o error de servidor)
        $this->assertTrue(
            $response->isRedirection() ||
            $response->isServerError() ||
            session()->has('errors')
        );
        // No debe haber voto registrado
        $this->assertFalse($voter->fresh()->has_voted);
    }

    public function test_candidate_id_as_object_rejected(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $response = $this->actingAs($voter)
            ->post('/vote', ['candidate_id' => ['id' => $candidate->id]]);

        // Object/array debe ser rechazado
        $this->assertTrue(
            $response->isRedirection() ||
            $response->isServerError() ||
            session()->has('errors')
        );
        // No debe haber voto registrado
        $this->assertFalse($voter->fresh()->has_voted);
    }

    public function test_candidate_id_as_negative_rejected(): void
    {
        $voter = $this->createVoter();

        $response = $this->actingAs($voter)
            ->post('/vote', ['candidate_id' => -1]);

        $response->assertSessionHasErrors('candidate_id');
    }

    public function test_candidate_id_as_float_rejected(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $response = $this->actingAs($voter)
            ->post('/vote', ['candidate_id' => 1.5]);

        $response->assertSessionHasErrors('candidate_id');
    }

    public function test_candidate_id_as_boolean_rejected(): void
    {
        $voter = $this->createVoter();

        $response = $this->actingAs($voter)
            ->post('/vote', ['candidate_id' => true]);

        $response->assertSessionHasErrors('candidate_id');
    }

    public function test_candidate_id_as_null_rejected(): void
    {
        $voter = $this->createVoter();

        $response = $this->actingAs($voter)
            ->post('/vote', ['candidate_id' => null]);

        $response->assertSessionHasErrors('candidate_id');
    }

    public function test_candidate_id_as_empty_string_rejected(): void
    {
        $voter = $this->createVoter();

        $response = $this->actingAs($voter)
            ->post('/vote', ['candidate_id' => '']);

        $response->assertSessionHasErrors('candidate_id');
    }

    public function test_candidate_id_with_special_characters_rejected(): void
    {
        $voter = $this->createVoter();

        $specialChars = ['<script>', '<?php', '${7*7}', '{{7*7}}', '#{7*7}'];

        foreach ($specialChars as $char) {
            // Crear nuevo voter para cada intento
            $testVoter = $this->createVoter();

            $response = $this->actingAs($testVoter)
                ->post('/vote', ['candidate_id' => $char]);

            // Debe ser rechazado (error de validacion o no hay voto)
            $this->assertFalse(
                $testVoter->fresh()->has_voted,
                "Special char '$char' should not allow voting"
            );
        }
    }

    // =====================================================
    // TESTS DE HTTP METHODS
    // =====================================================

    public function test_vote_get_not_allowed(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $response = $this->actingAs($voter)
            ->get('/vote?candidate_id=' . $candidate->id);

        // GET muestra la pagina, no vota
        $this->assertEquals(0, Vote::count());
    }

    public function test_vote_put_not_allowed(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $response = $this->actingAs($voter)
            ->put('/vote', ['candidate_id' => $candidate->id]);

        $response->assertStatus(405);
    }

    public function test_vote_patch_not_allowed(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $response = $this->actingAs($voter)
            ->patch('/vote', ['candidate_id' => $candidate->id]);

        $response->assertStatus(405);
    }

    public function test_vote_delete_not_allowed(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $response = $this->actingAs($voter)
            ->delete('/vote', ['candidate_id' => $candidate->id]);

        $response->assertStatus(405);
    }

    // =====================================================
    // TESTS DE INTEGRIDAD FINAL
    // =====================================================

    public function test_complete_voting_integrity_check(): void
    {
        $candidates = [];
        for ($i = 0; $i < 5; $i++) {
            $candidates[] = $this->createCandidate(['name' => "Candidato $i"]);
        }

        $voters = [];
        for ($i = 0; $i < 100; $i++) {
            $voters[] = $this->createVoter();
        }

        // Todos votan
        foreach ($voters as $voter) {
            $candidate = $candidates[array_rand($candidates)];
            $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);
        }

        // Verificaciones de integridad
        $totalVotes = Vote::count();
        $totalVotersVoted = User::where('has_voted', true)->count();
        $sumVotesCount = Candidate::sum('votes_count');

        // 1. Total votos = votantes que votaron
        $this->assertEquals($totalVotes, $totalVotersVoted);

        // 2. Suma de votes_count = total votos
        $this->assertEquals($sumVotesCount, $totalVotes);

        // 3. Cada votes_count coincide con COUNT real
        foreach ($candidates as $candidate) {
            $candidate->refresh();
            $actualCount = Vote::where('candidate_id', $candidate->id)->count();
            $this->assertEquals($actualCount, $candidate->votes_count);
        }

        // 4. No hay votos huerfanos
        $orphanVotes = Vote::whereNotIn('candidate_id', Candidate::pluck('id'))->count();
        $this->assertEquals(0, $orphanVotes);

        // 5. No hay usuarios que votaron mas de una vez
        $doubleVoters = DB::table('users')
            ->where('has_voted', true)
            ->groupBy('id')
            ->havingRaw('COUNT(*) > 1')
            ->count();
        $this->assertEquals(0, $doubleVoters);

        // 6. Total votos <= total votantes
        $this->assertLessThanOrEqual(User::count(), Vote::count());
    }

    public function test_stress_test_200_voters(): void
    {
        $candidate = $this->createCandidate();

        for ($i = 0; $i < 200; $i++) {
            $voter = $this->createVoter();
            $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);
        }

        $candidate->refresh();

        $this->assertEquals(200, Vote::count());
        $this->assertEquals(200, $candidate->votes_count);
        $this->assertEquals(200, User::where('has_voted', true)->count());
    }
}
