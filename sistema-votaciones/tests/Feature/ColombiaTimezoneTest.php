<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Candidate;
use App\Models\User;
use App\Models\Vote;
use App\Models\VotingSetting;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ColombiaTimezoneTest extends TestCase
{
    use RefreshDatabase;

    private string $colombiaTimezone = 'America/Bogota';

    protected function setUp(): void
    {
        parent::setUp();

        // Crear admin de prueba
        Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
    }

    // =====================================================
    // TESTS DE HORA DE COLOMBIA BASICOS
    // =====================================================

    public function test_carbon_colombia_timezone_is_correct(): void
    {
        $colombiaTime = Carbon::now($this->colombiaTimezone);

        // Colombia es UTC-5 (offset -18000 segundos)
        $expectedOffset = -5 * 60 * 60; // -18000 segundos

        $this->assertEquals($expectedOffset, $colombiaTime->offset);
        $this->assertEquals('America/Bogota', $colombiaTime->timezone->getName());
    }

    public function test_colombia_time_format_is_correct(): void
    {
        $colombiaTime = Carbon::now($this->colombiaTimezone);

        // Verificar formato de fecha
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $colombiaTime->format('Y-m-d'));
        $this->assertMatchesRegularExpression('/^\d{2}:\d{2}:\d{2}$/', $colombiaTime->format('H:i:s'));
    }

    public function test_voting_setting_uses_colombia_time(): void
    {
        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->subHour(),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->addHour(),
        ]);

        // El metodo privado colombiaTime() debe usar Colombia
        $reflection = new \ReflectionClass($settings);
        $method = $reflection->getMethod('colombiaTime');
        $method->setAccessible(true);

        $colombiaTime = $method->invoke($settings);

        $this->assertEquals($this->colombiaTimezone, $colombiaTime->timezone->getName());
    }

    // =====================================================
    // TESTS DE AUTO-APERTURA
    // =====================================================

    public function test_should_auto_open_when_start_time_reached(): void
    {
        $settings = VotingSetting::create([
            'is_active' => false,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->subMinute(),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->addHour(),
        ]);

        $this->assertTrue($settings->shouldAutoOpen());
    }

    public function test_should_not_auto_open_when_start_time_in_future(): void
    {
        $settings = VotingSetting::create([
            'is_active' => false,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->addMinute(),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->addHour(),
        ]);

        $this->assertFalse($settings->shouldAutoOpen());
    }

    public function test_should_not_auto_open_when_already_active(): void
    {
        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->subMinute(),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->addHour(),
        ]);

        $this->assertFalse($settings->shouldAutoOpen());
    }

    public function test_should_not_auto_open_without_start_datetime(): void
    {
        $settings = VotingSetting::create([
            'is_active' => false,
            'start_datetime' => null,
            'end_datetime' => Carbon::now($this->colombiaTimezone)->addHour(),
        ]);

        $this->assertFalse($settings->shouldAutoOpen());
    }

    public function test_auto_open_exactly_at_start_time(): void
    {
        $exactTime = Carbon::now($this->colombiaTimezone);

        $settings = VotingSetting::create([
            'is_active' => false,
            'start_datetime' => $exactTime,
            'end_datetime' => $exactTime->copy()->addHour(),
        ]);

        // Debe abrir cuando el tiempo es igual
        $this->assertTrue($settings->shouldAutoOpen());
    }

    public function test_auto_open_one_second_before_start_time(): void
    {
        $settings = VotingSetting::create([
            'is_active' => false,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->addSecond(),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->addHour(),
        ]);

        $this->assertFalse($settings->shouldAutoOpen());
    }

    public function test_auto_open_one_second_after_start_time(): void
    {
        $settings = VotingSetting::create([
            'is_active' => false,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->subSecond(),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->addHour(),
        ]);

        $this->assertTrue($settings->shouldAutoOpen());
    }

    // =====================================================
    // TESTS DE AUTO-CIERRE
    // =====================================================

    public function test_should_auto_close_when_end_time_reached(): void
    {
        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->subHour(),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->subMinute(),
        ]);

        $this->assertTrue($settings->shouldAutoClose());
    }

    public function test_should_not_auto_close_when_end_time_in_future(): void
    {
        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->subHour(),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->addMinute(),
        ]);

        $this->assertFalse($settings->shouldAutoClose());
    }

    public function test_should_not_auto_close_when_inactive(): void
    {
        $settings = VotingSetting::create([
            'is_active' => false,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->subHour(),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->subMinute(),
        ]);

        $this->assertFalse($settings->shouldAutoClose());
    }

    public function test_should_not_auto_close_without_end_datetime(): void
    {
        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->subHour(),
            'end_datetime' => null,
        ]);

        $this->assertFalse($settings->shouldAutoClose());
    }

    public function test_auto_close_exactly_at_end_time(): void
    {
        $exactTime = Carbon::now($this->colombiaTimezone);

        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => $exactTime->copy()->subHour(),
            'end_datetime' => $exactTime,
        ]);

        $this->assertTrue($settings->shouldAutoClose());
    }

    public function test_auto_close_one_second_before_end_time(): void
    {
        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->subHour(),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->addSecond(),
        ]);

        $this->assertFalse($settings->shouldAutoClose());
    }

    public function test_auto_close_one_second_after_end_time(): void
    {
        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->subHour(),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->subSecond(),
        ]);

        $this->assertTrue($settings->shouldAutoClose());
    }

    // =====================================================
    // TESTS DE isVotingOpen()
    // =====================================================

    public function test_is_voting_open_when_active_and_in_range(): void
    {
        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->subHour(),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->addHour(),
        ]);

        $this->assertTrue($settings->isVotingOpen());
    }

    public function test_is_voting_open_false_when_inactive(): void
    {
        $settings = VotingSetting::create([
            'is_active' => false,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->subHour(),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->addHour(),
        ]);

        $this->assertFalse($settings->isVotingOpen());
    }

    public function test_is_voting_open_false_before_start_time(): void
    {
        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->addHour(),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->addHours(2),
        ]);

        $this->assertFalse($settings->isVotingOpen());
    }

    public function test_is_voting_open_false_after_end_time(): void
    {
        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->subHours(2),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->subHour(),
        ]);

        $this->assertFalse($settings->isVotingOpen());
    }

    public function test_is_voting_open_true_without_dates(): void
    {
        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => null,
            'end_datetime' => null,
        ]);

        $this->assertTrue($settings->isVotingOpen());
    }

    public function test_is_voting_open_true_with_only_start_date_passed(): void
    {
        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->subHour(),
            'end_datetime' => null,
        ]);

        $this->assertTrue($settings->isVotingOpen());
    }

    public function test_is_voting_open_true_with_only_end_date_in_future(): void
    {
        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => null,
            'end_datetime' => Carbon::now($this->colombiaTimezone)->addHour(),
        ]);

        $this->assertTrue($settings->isVotingOpen());
    }

    // =====================================================
    // TESTS DE REGISTRO DE HORA DE VOTO
    // =====================================================

    public function test_vote_records_colombia_time(): void
    {
        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->subHour(),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->addHour(),
        ]);

        $user = User::create([
            'cedula' => '123456789',
            'password' => Hash::make('Password123'),
            'must_change_password' => false,
        ]);

        $candidate = Candidate::create([
            'name' => 'Test Candidate',
            'is_active' => true,
            'position' => 1,
        ]);

        $beforeVote = Carbon::now();

        $this->actingAs($user)
            ->post('/vote', ['candidate_id' => $candidate->id]);

        $user->refresh();

        $this->assertTrue($user->has_voted);
        $this->assertNotNull($user->voted_at);

        // La hora de voto debe ser posterior o igual a antes de votar
        $this->assertTrue($user->voted_at->gte($beforeVote->subSecond()));
    }

    public function test_vote_timestamp_in_vote_table(): void
    {
        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->subHour(),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->addHour(),
        ]);

        $user = User::create([
            'cedula' => '123456789',
            'password' => Hash::make('Password123'),
            'must_change_password' => false,
        ]);

        $candidate = Candidate::create([
            'name' => 'Test Candidate',
            'is_active' => true,
            'position' => 1,
        ]);

        $beforeVote = Carbon::now();

        $this->actingAs($user)
            ->post('/vote', ['candidate_id' => $candidate->id]);

        $vote = Vote::first();

        $this->assertNotNull($vote);
        $this->assertNotNull($vote->voted_at);
        $this->assertNotNull($vote->created_at);

        // Verificar que las fechas son recientes
        $this->assertTrue($vote->voted_at->gte($beforeVote->subSecond()));
        $this->assertTrue($vote->created_at->gte($beforeVote->subSecond()));
    }

    // =====================================================
    // TESTS DE VALIDACION DE FECHAS EN SETTINGS
    // =====================================================

    public function test_end_date_must_be_after_start_date(): void
    {
        $admin = Admin::first();

        VotingSetting::create([
            'is_active' => false,
            'start_datetime' => null,
            'end_datetime' => null,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->post('/admin/settings', [
                'start_datetime' => Carbon::now($this->colombiaTimezone)->addHour()->format('Y-m-d\TH:i'),
                'end_datetime' => Carbon::now($this->colombiaTimezone)->subHour()->format('Y-m-d\TH:i'),
            ]);

        $response->assertSessionHasErrors('end_datetime');
    }

    public function test_can_set_valid_date_range(): void
    {
        $admin = Admin::first();

        VotingSetting::create([
            'is_active' => false,
            'start_datetime' => null,
            'end_datetime' => null,
        ]);

        $startDate = Carbon::now($this->colombiaTimezone)->addHour();
        $endDate = Carbon::now($this->colombiaTimezone)->addHours(2);

        $response = $this->actingAs($admin, 'admin')
            ->post('/admin/settings', [
                'start_datetime' => $startDate->format('Y-m-d\TH:i'),
                'end_datetime' => $endDate->format('Y-m-d\TH:i'),
            ]);

        $response->assertSessionDoesntHaveErrors();
        $response->assertRedirect();

        $settings = VotingSetting::first();
        $this->assertNotNull($settings->start_datetime);
        $this->assertNotNull($settings->end_datetime);
    }

    public function test_cannot_open_voting_with_past_end_date(): void
    {
        $admin = Admin::first();

        $settings = VotingSetting::create([
            'is_active' => false,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->subHours(2),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->subHour(),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->post('/admin/settings/toggle');

        $response->assertSessionHas('error');

        $settings->refresh();
        $this->assertFalse($settings->is_active);
    }

    public function test_cannot_open_voting_without_dates(): void
    {
        $admin = Admin::first();

        $settings = VotingSetting::create([
            'is_active' => false,
            'start_datetime' => null,
            'end_datetime' => null,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->post('/admin/settings/toggle');

        $response->assertSessionHas('error');

        $settings->refresh();
        $this->assertFalse($settings->is_active);
    }

    public function test_can_open_voting_with_valid_future_dates(): void
    {
        $admin = Admin::first();

        $settings = VotingSetting::create([
            'is_active' => false,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->subMinute(),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->addHour(),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->post('/admin/settings/toggle');

        $response->assertSessionHas('success');

        $settings->refresh();
        $this->assertTrue($settings->is_active);
    }

    // =====================================================
    // TESTS DE EDGE CASES ZONA HORARIA
    // =====================================================

    public function test_voting_around_midnight_colombia(): void
    {
        // Simular votacion que cruza la medianoche
        $beforeMidnight = Carbon::today($this->colombiaTimezone)->setTime(23, 30, 0);
        $afterMidnight = Carbon::tomorrow($this->colombiaTimezone)->setTime(0, 30, 0);

        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => $beforeMidnight,
            'end_datetime' => $afterMidnight,
        ]);

        // Si estamos en ese rango, deberia estar abierto
        Carbon::setTestNow($beforeMidnight->copy()->addMinutes(15));
        $this->assertTrue($settings->isVotingOpen());

        Carbon::setTestNow($afterMidnight->copy()->subMinutes(15));
        $this->assertTrue($settings->isVotingOpen());

        Carbon::setTestNow(); // Reset
    }

    public function test_voting_exactly_at_midnight(): void
    {
        $midnight = Carbon::today($this->colombiaTimezone)->addDay()->setTime(0, 0, 0);

        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => $midnight->copy()->subHour(),
            'end_datetime' => $midnight,
        ]);

        Carbon::setTestNow($midnight);

        // Exactamente a medianoche, ya deberia cerrar (gte)
        $this->assertTrue($settings->shouldAutoClose());

        Carbon::setTestNow();
    }

    public function test_voting_across_day_boundary(): void
    {
        $startDay1 = Carbon::now($this->colombiaTimezone)->startOfDay()->setTime(8, 0, 0);
        $endDay2 = Carbon::now($this->colombiaTimezone)->addDay()->startOfDay()->setTime(18, 0, 0);

        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => $startDay1,
            'end_datetime' => $endDay2,
        ]);

        // Dia 1, 12:00
        Carbon::setTestNow($startDay1->copy()->setTime(12, 0, 0));
        $this->assertTrue($settings->isVotingOpen());

        // Dia 2, 12:00
        Carbon::setTestNow($endDay2->copy()->subHours(6));
        $this->assertTrue($settings->isVotingOpen());

        // Dia 2, 19:00 (despues del cierre)
        Carbon::setTestNow($endDay2->copy()->addHour());
        $this->assertFalse($settings->isVotingOpen());

        Carbon::setTestNow();
    }

    public function test_voting_across_month_boundary(): void
    {
        // Ultimo dia del mes al primero del siguiente
        $endOfMonth = Carbon::now($this->colombiaTimezone)->endOfMonth()->setTime(20, 0, 0);
        $startOfNextMonth = Carbon::now($this->colombiaTimezone)->addMonth()->startOfMonth()->setTime(8, 0, 0);

        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => $endOfMonth,
            'end_datetime' => $startOfNextMonth,
        ]);

        // Durante el rango
        Carbon::setTestNow($endOfMonth->copy()->addHours(2));
        $this->assertTrue($settings->isVotingOpen());

        Carbon::setTestNow();
    }

    public function test_voting_across_year_boundary(): void
    {
        // 31 Dic al 1 Ene
        $endOfYear = Carbon::create(2025, 12, 31, 20, 0, 0, $this->colombiaTimezone);
        $startOfYear = Carbon::create(2026, 1, 1, 8, 0, 0, $this->colombiaTimezone);

        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => $endOfYear,
            'end_datetime' => $startOfYear,
        ]);

        // 31 Dic 23:00
        Carbon::setTestNow(Carbon::create(2025, 12, 31, 23, 0, 0, $this->colombiaTimezone));
        $this->assertTrue($settings->isVotingOpen());

        // 1 Ene 01:00
        Carbon::setTestNow(Carbon::create(2026, 1, 1, 1, 0, 0, $this->colombiaTimezone));
        $this->assertTrue($settings->isVotingOpen());

        Carbon::setTestNow();
    }

    // =====================================================
    // TESTS DE PRECISION DE COUNTDOWN
    // =====================================================

    public function test_countdown_precision_seconds(): void
    {
        $now = Carbon::now($this->colombiaTimezone);
        $futureTime = $now->copy()->addSeconds(30);

        // La diferencia debe ser 30 segundos (absoluto)
        $diff = $now->diffInSeconds($futureTime, false);

        $this->assertEqualsWithDelta(30, $diff, 2);
    }

    public function test_countdown_precision_minutes(): void
    {
        $now = Carbon::now($this->colombiaTimezone);
        $futureTime = $now->copy()->addMinutes(5);

        $diff = $now->diffInMinutes($futureTime, false);

        $this->assertEqualsWithDelta(5, $diff, 1);
    }

    public function test_countdown_precision_hours(): void
    {
        $now = Carbon::now($this->colombiaTimezone);
        $futureTime = $now->copy()->addHours(2);

        $diff = $now->diffInHours($futureTime, false);

        $this->assertEquals(2, $diff);
    }

    public function test_countdown_precision_days(): void
    {
        $now = Carbon::now($this->colombiaTimezone);
        $futureTime = $now->copy()->addDays(3);

        $diff = $now->diffInDays($futureTime, false);

        $this->assertEquals(3, $diff);
    }

    // =====================================================
    // TESTS DE MIDDLEWARE voting.open
    // =====================================================

    public function test_middleware_blocks_vote_when_closed(): void
    {
        $settings = VotingSetting::create([
            'is_active' => false,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->subHour(),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->addHour(),
        ]);

        $user = User::create([
            'cedula' => '123456789',
            'password' => Hash::make('Password123'),
            'must_change_password' => false,
        ]);

        $response = $this->actingAs($user)->get('/vote');

        $response->assertRedirect(route('voting.closed'));
    }

    public function test_middleware_allows_vote_when_open(): void
    {
        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->subHour(),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->addHour(),
        ]);

        $user = User::create([
            'cedula' => '123456789',
            'password' => Hash::make('Password123'),
            'must_change_password' => false,
        ]);

        Candidate::create([
            'name' => 'Test',
            'is_active' => true,
            'position' => 1,
        ]);

        $response = $this->actingAs($user)->get('/vote');

        $response->assertStatus(200);
    }

    public function test_middleware_blocks_before_start_time(): void
    {
        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->addHour(),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->addHours(2),
        ]);

        $user = User::create([
            'cedula' => '123456789',
            'password' => Hash::make('Password123'),
            'must_change_password' => false,
        ]);

        $response = $this->actingAs($user)->get('/vote');

        $response->assertRedirect(route('voting.closed'));
    }

    public function test_middleware_blocks_after_end_time(): void
    {
        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->subHours(2),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->subHour(),
        ]);

        $user = User::create([
            'cedula' => '123456789',
            'password' => Hash::make('Password123'),
            'must_change_password' => false,
        ]);

        $response = $this->actingAs($user)->get('/vote');

        $response->assertRedirect(route('voting.closed'));
    }

    // =====================================================
    // TESTS DE COMPARACION TIMESTAMPS PHP VS JS
    // =====================================================

    public function test_timestamp_format_for_javascript(): void
    {
        $colombiaTime = Carbon::now($this->colombiaTimezone);

        // El timestamp Unix es universal (no depende de timezone)
        $timestamp = $colombiaTime->timestamp;

        // Verificar que es un entero valido
        $this->assertIsInt($timestamp);
        $this->assertGreaterThan(0, $timestamp);

        // Convertir de vuelta y verificar
        $fromTimestamp = Carbon::createFromTimestamp($timestamp, $this->colombiaTimezone);

        // Deben ser iguales (ignorando microsegundos)
        $this->assertEquals(
            $colombiaTime->format('Y-m-d H:i:s'),
            $fromTimestamp->format('Y-m-d H:i:s')
        );
    }

    public function test_timestamp_consistency_across_timezones(): void
    {
        $colombiaTime = Carbon::now($this->colombiaTimezone);
        $utcTime = Carbon::now('UTC');

        // Los timestamps deben ser muy cercanos (diferencia por ejecucion)
        $this->assertEqualsWithDelta(
            $colombiaTime->timestamp,
            $utcTime->timestamp,
            2 // Maximo 2 segundos de diferencia por ejecucion
        );
    }

    public function test_datetime_local_input_format(): void
    {
        $colombiaTime = Carbon::now($this->colombiaTimezone);

        // Formato para datetime-local HTML input
        $htmlFormat = $colombiaTime->format('Y-m-d\TH:i');

        // Verificar formato correcto
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $htmlFormat);

        // Verificar que se puede parsear de vuelta
        $parsed = Carbon::createFromFormat('Y-m-d\TH:i', $htmlFormat);
        $this->assertInstanceOf(Carbon::class, $parsed);
    }

    // =====================================================
    // TESTS DE CAMBIO DE DIA/MES/ANO
    // =====================================================

    public function test_voting_on_leap_year_february_29(): void
    {
        // 2024 es ano bisiesto
        $feb29 = Carbon::create(2024, 2, 29, 12, 0, 0, $this->colombiaTimezone);

        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => $feb29->copy()->subHour(),
            'end_datetime' => $feb29->copy()->addHour(),
        ]);

        Carbon::setTestNow($feb29);
        $this->assertTrue($settings->isVotingOpen());
        Carbon::setTestNow();
    }

    public function test_voting_on_daylight_saving_time_change(): void
    {
        // Colombia no tiene DST, pero probamos la estabilidad
        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->subDay(),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->addDay(),
        ]);

        // Verificar que UTC-5 se mantiene constante
        $time1 = Carbon::now($this->colombiaTimezone);
        $time2 = Carbon::now($this->colombiaTimezone)->addHours(12);

        $this->assertEquals($time1->offset, $time2->offset);
    }

    public function test_voting_on_last_day_of_month(): void
    {
        $lastDay = Carbon::now($this->colombiaTimezone)->endOfMonth()->setTime(12, 0, 0);

        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => $lastDay->copy()->subHour(),
            'end_datetime' => $lastDay->copy()->addHour(),
        ]);

        Carbon::setTestNow($lastDay);
        $this->assertTrue($settings->isVotingOpen());
        Carbon::setTestNow();
    }

    public function test_voting_on_first_day_of_month(): void
    {
        $firstDay = Carbon::now($this->colombiaTimezone)->startOfMonth()->setTime(12, 0, 0);

        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => $firstDay->copy()->subHour(),
            'end_datetime' => $firstDay->copy()->addHour(),
        ]);

        Carbon::setTestNow($firstDay);
        $this->assertTrue($settings->isVotingOpen());
        Carbon::setTestNow();
    }

    // =====================================================
    // TESTS DE AUTO-APERTURA/CIERRE EN CONTROLLER
    // =====================================================

    public function test_settings_controller_auto_opens_voting(): void
    {
        $admin = Admin::first();

        // Crear settings que deberia auto-abrir
        $settings = VotingSetting::create([
            'is_active' => false,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->subMinute(),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->addHour(),
        ]);

        // Visitar la pagina de settings deberia activar auto-open
        $response = $this->actingAs($admin, 'admin')->get('/admin/settings');

        $response->assertStatus(200);

        $settings->refresh();
        $this->assertTrue($settings->is_active);
    }

    public function test_settings_controller_auto_closes_voting(): void
    {
        $admin = Admin::first();

        // Crear settings que deberia auto-cerrar
        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->subHours(2),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->subMinute(),
        ]);

        // Visitar la pagina de settings deberia activar auto-close
        $response = $this->actingAs($admin, 'admin')->get('/admin/settings');

        $response->assertStatus(200);

        $settings->refresh();
        $this->assertFalse($settings->is_active);
    }

    public function test_settings_controller_does_not_auto_open_if_end_passed(): void
    {
        $admin = Admin::first();

        // Crear settings donde start y end ya pasaron
        $settings = VotingSetting::create([
            'is_active' => false,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->subHours(2),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->subHour(),
        ]);

        // Visitar la pagina de settings NO deberia activar porque end ya paso
        $response = $this->actingAs($admin, 'admin')->get('/admin/settings');

        $response->assertStatus(200);

        $settings->refresh();
        $this->assertFalse($settings->is_active);
    }

    // =====================================================
    // TESTS DE ESCENARIOS COMPLEJOS
    // =====================================================

    public function test_rapid_open_close_sequence(): void
    {
        $admin = Admin::first();

        $settings = VotingSetting::create([
            'is_active' => false,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->subMinute(),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->addHour(),
        ]);

        // Abrir
        $this->actingAs($admin, 'admin')->post('/admin/settings/toggle');
        $settings->refresh();
        $this->assertTrue($settings->is_active);

        // Cerrar
        $this->actingAs($admin, 'admin')->post('/admin/settings/toggle');
        $settings->refresh();
        $this->assertFalse($settings->is_active);

        // Abrir de nuevo
        $this->actingAs($admin, 'admin')->post('/admin/settings/toggle');
        $settings->refresh();
        $this->assertTrue($settings->is_active);
    }

    public function test_voting_window_very_short_1_minute(): void
    {
        $startTime = Carbon::now($this->colombiaTimezone);
        $endTime = $startTime->copy()->addMinute();

        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => $startTime,
            'end_datetime' => $endTime,
        ]);

        $this->assertTrue($settings->isVotingOpen());

        // Simular que pasa 1 minuto
        Carbon::setTestNow($endTime->copy()->addSecond());
        $this->assertFalse($settings->isVotingOpen());
        $this->assertTrue($settings->shouldAutoClose());
        Carbon::setTestNow();
    }

    public function test_voting_window_very_long_30_days(): void
    {
        $startTime = Carbon::now($this->colombiaTimezone)->subDays(15);
        $endTime = Carbon::now($this->colombiaTimezone)->addDays(15);

        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => $startTime,
            'end_datetime' => $endTime,
        ]);

        $this->assertTrue($settings->isVotingOpen());
        $this->assertFalse($settings->shouldAutoClose());
    }

    public function test_multiple_votes_same_second(): void
    {
        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->subHour(),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->addHour(),
        ]);

        $candidate = Candidate::create([
            'name' => 'Test',
            'is_active' => true,
            'position' => 1,
        ]);

        $users = [];
        for ($i = 0; $i < 5; $i++) {
            $users[] = User::create([
                'cedula' => '12345678' . $i,
                'password' => Hash::make('Password123'),
                'must_change_password' => false,
            ]);
        }

        // Votar todos en el mismo segundo
        foreach ($users as $user) {
            $this->actingAs($user)->post('/vote', ['candidate_id' => $candidate->id]);
        }

        // Verificar que todos votaron
        $this->assertEquals(5, Vote::count());
        $this->assertEquals(5, User::where('has_voted', true)->count());

        // Verificar que todos tienen voted_at
        foreach (User::where('has_voted', true)->get() as $user) {
            $this->assertNotNull($user->voted_at);
        }
    }

    public function test_timestamp_json_encoding_for_javascript(): void
    {
        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->addMinutes(5),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->addHour(),
        ]);

        // Simular lo que hace la vista Blade
        $startTimestamp = json_encode($settings->start_datetime?->timestamp);
        $endTimestamp = json_encode($settings->end_datetime?->timestamp);

        // Verificar que son numeros validos
        $this->assertIsNumeric(json_decode($startTimestamp));
        $this->assertIsNumeric(json_decode($endTimestamp));

        // Verificar que end > start
        $this->assertGreaterThan(
            json_decode($startTimestamp),
            json_decode($endTimestamp)
        );
    }

    // =====================================================
    // TESTS DE FORMATO DE HORA PARA USUARIO
    // =====================================================

    public function test_voted_at_display_format(): void
    {
        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->subHour(),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->addHour(),
        ]);

        $user = User::create([
            'cedula' => '123456789',
            'password' => Hash::make('Password123'),
            'must_change_password' => false,
        ]);

        $candidate = Candidate::create([
            'name' => 'Test',
            'is_active' => true,
            'position' => 1,
        ]);

        $this->actingAs($user)->post('/vote', ['candidate_id' => $candidate->id]);

        $user->refresh();

        // Verificar formato de fecha
        $dateFormat = $user->voted_at->format('d/m/Y');
        $timeFormat = $user->voted_at->format('H:i:s');

        $this->assertMatchesRegularExpression('/^\d{2}\/\d{2}\/\d{4}$/', $dateFormat);
        $this->assertMatchesRegularExpression('/^\d{2}:\d{2}:\d{2}$/', $timeFormat);
    }

    public function test_colombia_time_offset_is_minus_5(): void
    {
        $colombiaTime = Carbon::now($this->colombiaTimezone);

        // Colombia es UTC-5 (offset -18000 segundos o -300 minutos)
        $expectedOffsetSeconds = -5 * 60 * 60; // -18000

        $this->assertEquals($expectedOffsetSeconds, $colombiaTime->offset);
    }

    public function test_carbon_parse_datetime_local_input(): void
    {
        // Simular input de datetime-local
        $input = '2026-02-15T14:30';

        $parsed = Carbon::createFromFormat('Y-m-d\TH:i', $input);

        $this->assertEquals(2026, $parsed->year);
        $this->assertEquals(2, $parsed->month);
        $this->assertEquals(15, $parsed->day);
        $this->assertEquals(14, $parsed->hour);
        $this->assertEquals(30, $parsed->minute);
    }

    // =====================================================
    // TESTS DE INTEGRIDAD FINAL
    // =====================================================

    public function test_all_time_operations_use_colombia_timezone(): void
    {
        $settings = new VotingSetting([
            'is_active' => true,
            'start_datetime' => Carbon::now($this->colombiaTimezone)->subHour(),
            'end_datetime' => Carbon::now($this->colombiaTimezone)->addHour(),
        ]);

        // Usar reflection para verificar el metodo privado
        $reflection = new \ReflectionClass($settings);
        $method = $reflection->getMethod('colombiaTime');
        $method->setAccessible(true);

        $time1 = $method->invoke($settings);
        $time2 = $method->invoke($settings);

        // Ambos deben usar timezone Colombia
        $this->assertEquals('America/Bogota', $time1->timezone->getName());
        $this->assertEquals('America/Bogota', $time2->timezone->getName());
    }

    public function test_database_stores_correct_datetime(): void
    {
        $colombiaTime = Carbon::now($this->colombiaTimezone);

        $settings = VotingSetting::create([
            'is_active' => true,
            'start_datetime' => $colombiaTime,
            'end_datetime' => $colombiaTime->copy()->addHour(),
        ]);

        // Recuperar de la base de datos
        $retrieved = VotingSetting::find($settings->id);

        // Las fechas deben coincidir (formato Y-m-d H:i:s)
        $this->assertEquals(
            $colombiaTime->format('Y-m-d H:i:s'),
            $retrieved->start_datetime->format('Y-m-d H:i:s')
        );
    }
}
