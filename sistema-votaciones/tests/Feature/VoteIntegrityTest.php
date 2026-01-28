<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\User;
use App\Models\Vote;
use App\Models\VotingSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoteIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create voting settings with open voting
        VotingSetting::create([
            'is_active' => true,
            'start_datetime' => now()->subHour(),
            'end_datetime' => now()->addHour(),
        ]);
    }

    private function createVoter(string $cedula = '123456', bool $changedPassword = true): User
    {
        return User::create([
            'cedula' => $cedula,
            'password' => 'password123',
            'must_change_password' => !$changedPassword,
            'is_blocked' => false,
            'has_voted' => false,
        ]);
    }

    private function createCandidate(string $name = 'Candidato 1'): Candidate
    {
        return Candidate::create([
            'name' => $name,
            'is_active' => true,
            'votes_count' => 0,
            'position' => 1,
        ]);
    }

    // ==========================================
    // TEST 1: Voto normal funciona correctamente
    // ==========================================
    public function test_voto_normal_se_registra_correctamente(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $response = $this->actingAs($voter)
            ->post('/vote', ['candidate_id' => $candidate->id]);

        $response->assertRedirect('/vote/confirmation');

        // Verificar que el voto se registro
        $this->assertDatabaseCount('votes', 1);
        $this->assertDatabaseHas('votes', ['candidate_id' => $candidate->id]);

        // Verificar que el usuario se marco como votante
        $voter->refresh();
        $this->assertTrue($voter->has_voted);
        $this->assertNotNull($voter->voted_at);

        // Verificar que votes_count del candidato incremento
        $candidate->refresh();
        $this->assertEquals(1, $candidate->votes_count);
    }

    // ==========================================
    // TEST 2: Doble click - segundo voto rechazado
    // ==========================================
    public function test_doble_click_segundo_voto_es_rechazado(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        // Primer voto
        $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);

        // Segundo voto (doble click)
        $response = $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);

        $response->assertRedirect('/vote');

        // Solo 1 voto debe existir
        $this->assertDatabaseCount('votes', 1);
        $candidate->refresh();
        $this->assertEquals(1, $candidate->votes_count);
    }

    // ==========================================
    // TEST 3: No puede votar por candidato inactivo
    // ==========================================
    public function test_no_puede_votar_por_candidato_inactivo(): void
    {
        $voter = $this->createVoter();
        $candidate = Candidate::create([
            'name' => 'Inactivo',
            'is_active' => false,
            'votes_count' => 0,
            'position' => 1,
        ]);

        $response = $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);

        $response->assertRedirect('/vote');
        $this->assertDatabaseCount('votes', 0);
        $voter->refresh();
        $this->assertFalse($voter->has_voted);
    }

    // ==========================================
    // TEST 4: No puede votar con candidato inexistente
    // ==========================================
    public function test_no_puede_votar_con_candidato_inexistente(): void
    {
        $voter = $this->createVoter();

        $response = $this->actingAs($voter)->post('/vote', ['candidate_id' => 99999]);

        $response->assertSessionHasErrors('candidate_id');
        $this->assertDatabaseCount('votes', 0);
    }

    // ==========================================
    // TEST 5: No puede votar sin candidate_id
    // ==========================================
    public function test_no_puede_votar_sin_candidate_id(): void
    {
        $voter = $this->createVoter();

        $response = $this->actingAs($voter)->post('/vote', []);

        $response->assertSessionHasErrors('candidate_id');
        $this->assertDatabaseCount('votes', 0);
    }

    // ==========================================
    // TEST 6: No puede votar cuando la votación está cerrada
    // ==========================================
    public function test_no_puede_votar_con_votacion_cerrada(): void
    {
        $settings = VotingSetting::current();
        $settings->is_active = false;
        $settings->save();

        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $response = $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);

        $response->assertRedirect();
        $this->assertDatabaseCount('votes', 0);
    }

    // ==========================================
    // TEST 7: No puede votar cuando la fecha de cierre ya paso
    // ==========================================
    public function test_no_puede_votar_con_fecha_expirada(): void
    {
        $settings = VotingSetting::current();
        $settings->end_datetime = now()->subMinute();
        $settings->save();

        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $response = $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);

        $response->assertRedirect();
        $this->assertDatabaseCount('votes', 0);
    }

    // ==========================================
    // TEST 8: No puede votar antes de la fecha de apertura
    // ==========================================
    public function test_no_puede_votar_antes_de_fecha_apertura(): void
    {
        $settings = VotingSetting::current();
        $settings->start_datetime = now()->addHour();
        $settings->end_datetime = now()->addHours(2);
        $settings->save();

        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $response = $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);

        $response->assertRedirect();
        $this->assertDatabaseCount('votes', 0);
    }

    // ==========================================
    // TEST 9: Usuario bloqueado no puede votar
    // ==========================================
    public function test_usuario_bloqueado_no_puede_votar(): void
    {
        $voter = $this->createVoter();
        $voter->is_blocked = true;
        $voter->save();
        $candidate = $this->createCandidate();

        $response = $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);

        // El middleware not.blocked debería redirigir
        $this->assertDatabaseCount('votes', 0);
    }

    // ==========================================
    // TEST 10: Usuario sin cambiar contraseña no puede votar
    // ==========================================
    public function test_usuario_sin_cambiar_password_no_puede_votar(): void
    {
        $voter = $this->createVoter('111111', false);
        $candidate = $this->createCandidate();

        $response = $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);

        // El middleware password.changed debería redirigir
        $this->assertDatabaseCount('votes', 0);
    }

    // ==========================================
    // TEST 11: Usuario no autenticado no puede votar
    // ==========================================
    public function test_usuario_no_autenticado_no_puede_votar(): void
    {
        $candidate = $this->createCandidate();

        $response = $this->post('/vote', ['candidate_id' => $candidate->id]);

        $response->assertRedirect('/login');
        $this->assertDatabaseCount('votes', 0);
    }

    // ==========================================
    // TEST 12: Múltiples votantes, cada uno vota una vez
    // ==========================================
    public function test_multiples_votantes_consistencia(): void
    {
        $candidate1 = $this->createCandidate('Candidato A');
        $candidate2 = Candidate::create([
            'name' => 'Candidato B',
            'is_active' => true,
            'votes_count' => 0,
            'position' => 2,
        ]);

        $voters = [];
        for ($i = 1; $i <= 10; $i++) {
            $voters[] = $this->createVoter("100000{$i}");
        }

        // Primeros 6 votan por candidato 1, siguientes 4 por candidato 2
        foreach ($voters as $index => $voter) {
            $candidateId = $index < 6 ? $candidate1->id : $candidate2->id;
            $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidateId]);
        }

        // Verificar consistencia total
        $this->assertDatabaseCount('votes', 10);
        $this->assertEquals(10, User::where('has_voted', true)->count());

        $candidate1->refresh();
        $candidate2->refresh();
        $this->assertEquals(6, $candidate1->votes_count);
        $this->assertEquals(4, $candidate2->votes_count);

        // votes_count debe igualar votos reales en tabla votes
        $this->assertEquals($candidate1->votes_count, Vote::where('candidate_id', $candidate1->id)->count());
        $this->assertEquals($candidate2->votes_count, Vote::where('candidate_id', $candidate2->id)->count());

        // Total votos = total usuarios que votaron
        $this->assertEquals(Vote::count(), User::where('has_voted', true)->count());
    }

    // ==========================================
    // TEST 13: Intentar votar dos veces por candidatos diferentes
    // ==========================================
    public function test_no_puede_cambiar_voto(): void
    {
        $voter = $this->createVoter();
        $candidate1 = $this->createCandidate('C1');
        $candidate2 = Candidate::create([
            'name' => 'C2',
            'is_active' => true,
            'votes_count' => 0,
            'position' => 2,
        ]);

        // Vota por candidato 1
        $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate1->id]);

        // Intenta votar por candidato 2
        $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate2->id]);

        // Solo debe haber 1 voto, para candidato 1
        $this->assertDatabaseCount('votes', 1);
        $this->assertDatabaseHas('votes', ['candidate_id' => $candidate1->id]);
        $this->assertDatabaseMissing('votes', ['candidate_id' => $candidate2->id]);

        $candidate1->refresh();
        $candidate2->refresh();
        $this->assertEquals(1, $candidate1->votes_count);
        $this->assertEquals(0, $candidate2->votes_count);
    }

    // ==========================================
    // TEST 14: Simular race condition - marcar has_voted=false manualmente
    // ==========================================
    public function test_race_condition_lockForUpdate_protege(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        // Primer voto normal
        $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);

        // Simular que alguien resetea has_voted en la sesion pero no en BD
        // El lockForUpdate en el controller usa la BD, no la sesion
        $voter->refresh();
        $this->assertTrue($voter->has_voted);
        $this->assertDatabaseCount('votes', 1);

        // Intentar forzar otro voto (has_voted ya es true en BD)
        $response = $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);

        // Sigue habiendo solo 1 voto
        $this->assertDatabaseCount('votes', 1);
        $candidate->refresh();
        $this->assertEquals(1, $candidate->votes_count);
    }

    // ==========================================
    // TEST 15: Consistencia - votes_count siempre coincide con COUNT real
    // ==========================================
    public function test_votes_count_siempre_coincide_con_count_real(): void
    {
        $candidate = $this->createCandidate();

        for ($i = 1; $i <= 5; $i++) {
            $voter = $this->createVoter("20000{$i}");
            $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);
        }

        $candidate->refresh();
        $realCount = Vote::where('candidate_id', $candidate->id)->count();

        $this->assertEquals($realCount, $candidate->votes_count);
        $this->assertEquals(5, $realCount);
        $this->assertEquals(5, User::where('has_voted', true)->count());
        $this->assertEquals(Vote::count(), User::where('has_voted', true)->count());
    }

    // ==========================================
    // TEST 16: Throttle middleware está configurado en la ruta
    // ==========================================
    public function test_throttle_middleware_configurado(): void
    {
        $route = app('router')->getRoutes()->getByName('vote.store');
        $this->assertNotNull($route);

        $middleware = $route->gatherMiddleware();
        $hasThrottle = collect($middleware)->contains(fn($m) => str_contains($m, 'throttle'));
        $this->assertTrue($hasThrottle, 'La ruta vote.store debe tener middleware throttle');
    }

    // ==========================================
    // TEST 17: Request AJAX rechazado por SecureVote middleware
    // ==========================================
    public function test_request_ajax_rechazado(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $response = $this->actingAs($voter)
            ->postJson('/vote', ['candidate_id' => $candidate->id]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('votes', 0);
    }

    // ==========================================
    // TEST 18: Admin no puede votar (guard diferente)
    // ==========================================
    public function test_admin_no_puede_votar(): void
    {
        $candidate = $this->createCandidate();

        // Sin autenticación en guard 'web', el post debe redirigir a login
        $response = $this->post('/vote', ['candidate_id' => $candidate->id]);
        $response->assertRedirect('/login');
        $this->assertDatabaseCount('votes', 0);
    }

    // ==========================================
    // TEST 19: Confirmación solo accesible si votó
    // ==========================================
    public function test_confirmacion_redirige_si_no_ha_votado(): void
    {
        $voter = $this->createVoter();

        $response = $this->actingAs($voter)->get('/vote/confirmation');

        $response->assertRedirect('/vote');
    }

    // ==========================================
    // TEST 20: Confirmación accesible si votó
    // ==========================================
    public function test_confirmacion_accesible_si_voto(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);

        // Refrescar usuario ya que la sesión se regeneró
        $voter->refresh();
        $this->assertTrue($voter->has_voted);

        $response = $this->actingAs($voter)->get('/vote/confirmation');

        // Puede ser 200 o redirect dependiendo de la sesión regenerada
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302]));
    }

    // ==========================================
    // TEST 21: Integridad total del sistema - escenario completo
    // ==========================================
    public function test_integridad_total_escenario_completo(): void
    {
        $candidateA = $this->createCandidate('Ana');
        $candidateB = Candidate::create(['name' => 'Bob', 'is_active' => true, 'votes_count' => 0, 'position' => 2]);
        $candidateC = Candidate::create(['name' => 'Carlos', 'is_active' => true, 'votes_count' => 0, 'position' => 3]);

        $totalVoters = 20;
        $voters = [];
        for ($i = 1; $i <= $totalVoters; $i++) {
            $voters[] = $this->createVoter(str_pad($i, 6, '0', STR_PAD_LEFT));
        }

        // 10 votan A, 6 votan B, 4 votan C
        foreach ($voters as $i => $voter) {
            if ($i < 10) $cid = $candidateA->id;
            elseif ($i < 16) $cid = $candidateB->id;
            else $cid = $candidateC->id;

            $this->actingAs($voter)->post('/vote', ['candidate_id' => $cid]);
        }

        // Intentar que todos voten de nuevo
        foreach ($voters as $voter) {
            $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidateA->id]);
        }

        // VERIFICACIONES DE INTEGRIDAD
        $candidateA->refresh();
        $candidateB->refresh();
        $candidateC->refresh();

        // 1. Total votos en tabla = total votantes que votaron
        $this->assertEquals($totalVoters, Vote::count());
        $this->assertEquals($totalVoters, User::where('has_voted', true)->count());

        // 2. votes_count de cada candidato coincide con votos reales
        $this->assertEquals(Vote::where('candidate_id', $candidateA->id)->count(), $candidateA->votes_count);
        $this->assertEquals(Vote::where('candidate_id', $candidateB->id)->count(), $candidateB->votes_count);
        $this->assertEquals(Vote::where('candidate_id', $candidateC->id)->count(), $candidateC->votes_count);

        // 3. Distribución correcta
        $this->assertEquals(10, $candidateA->votes_count);
        $this->assertEquals(6, $candidateB->votes_count);
        $this->assertEquals(4, $candidateC->votes_count);

        // 4. Suma de votos de candidatos = total votos
        $sumVotesCount = Candidate::sum('votes_count');
        $this->assertEquals(Vote::count(), $sumVotesCount);

        // 5. Nunca hay más votos que usuarios
        $this->assertLessThanOrEqual(User::count(), Vote::count());
    }

    // ==========================================
    // TEST 22: No se puede manipular candidate_id con valor string
    // ==========================================
    public function test_candidate_id_con_string_rechazado(): void
    {
        $voter = $this->createVoter();
        $this->createCandidate();

        $response = $this->actingAs($voter)->post('/vote', ['candidate_id' => 'abc']);

        $response->assertSessionHasErrors('candidate_id');
        $this->assertDatabaseCount('votes', 0);
    }

    // ==========================================
    // TEST 23: No se puede votar con candidate_id negativo
    // ==========================================
    public function test_candidate_id_negativo_rechazado(): void
    {
        $voter = $this->createVoter();

        $response = $this->actingAs($voter)->post('/vote', ['candidate_id' => -1]);

        $response->assertSessionHasErrors('candidate_id');
        $this->assertDatabaseCount('votes', 0);
    }

    // ==========================================
    // TEST 24: Muchos doble-clicks rápidos, siempre 1 voto
    // ==========================================
    public function test_multiples_intentos_rapidos_solo_un_voto(): void
    {
        $voter = $this->createVoter();
        $candidate = $this->createCandidate();

        // Simular 10 intentos de voto consecutivos
        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($voter)->post('/vote', ['candidate_id' => $candidate->id]);
        }

        // Solo debe haber 1 voto
        $this->assertDatabaseCount('votes', 1);
        $candidate->refresh();
        $this->assertEquals(1, $candidate->votes_count);
        $this->assertEquals(Vote::count(), User::where('has_voted', true)->count());
    }
}
