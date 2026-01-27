<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Candidate;
use App\Models\Vote;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StressTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Iniciando prueba de estres...');

        // Verificar que hay candidatos
        $candidates = Candidate::where('is_active', true)->pluck('id')->toArray();
        if (empty($candidates)) {
            $this->command->error('No hay candidatos activos. Crea al menos un candidato primero.');
            return;
        }

        $this->command->info('Candidatos encontrados: ' . count($candidates));

        // Crear 500 votantes
        $this->command->info('Creando 500 votantes...');
        $voters = [];
        $baseTimestamp = now();

        DB::beginTransaction();
        try {
            for ($i = 1; $i <= 500; $i++) {
                $cedula = '9' . str_pad($i, 9, '0', STR_PAD_LEFT); // 9000000001, 9000000002, etc.

                $voter = User::create([
                    'cedula' => $cedula,
                    'password' => 'test1234',
                    'must_change_password' => false,
                    'is_blocked' => false,
                    'failed_attempts' => 0,
                    'has_voted' => false,
                ]);

                $voters[] = $voter;

                if ($i % 100 === 0) {
                    $this->command->info("  Creados {$i} votantes...");
                }
            }
            DB::commit();
            $this->command->info('500 votantes creados exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error creando votantes: ' . $e->getMessage());
            return;
        }

        // Simular 400 votos
        $this->command->info('Simulando 400 votos...');
        $votersToVote = array_slice($voters, 0, 400);
        $votesCreated = 0;
        $errors = 0;

        // Preparar datos para insercion masiva
        $votesData = [];
        $userUpdates = [];

        foreach ($votersToVote as $index => $voter) {
            $candidateId = $candidates[array_rand($candidates)];

            $votesData[] = [
                'candidate_id' => $candidateId,
                'voted_at' => now(),
                'created_at' => now(),
            ];

            $userUpdates[] = [
                'id' => $voter->id,
                'candidate_id' => $candidateId,
            ];
        }

        // Insertar votos en lotes
        $this->command->info('Insertando votos en la base de datos...');
        $startTime = microtime(true);

        DB::beginTransaction();
        try {
            // Insertar votos en chunks de 100
            $chunks = array_chunk($votesData, 100);
            foreach ($chunks as $chunkIndex => $chunk) {
                Vote::insert($chunk);
                $votesCreated += count($chunk);
                $this->command->info("  Insertados {$votesCreated} votos...");
            }

            // Actualizar contadores de candidatos
            $this->command->info('Actualizando contadores de candidatos...');
            $candidateVotes = [];
            foreach ($votesData as $vote) {
                $cid = $vote['candidate_id'];
                $candidateVotes[$cid] = ($candidateVotes[$cid] ?? 0) + 1;
            }

            foreach ($candidateVotes as $candidateId => $count) {
                Candidate::where('id', $candidateId)->increment('votes_count', $count);
            }

            // Marcar usuarios como que ya votaron
            $this->command->info('Marcando votantes como que ya votaron...');
            $voterIds = array_column($userUpdates, 'id');
            User::whereIn('id', $voterIds)->update([
                'has_voted' => true,
                'voted_at' => now(),
            ]);

            DB::commit();

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            $this->command->newLine();
            $this->command->info('========================================');
            $this->command->info('PRUEBA DE ESTRES COMPLETADA');
            $this->command->info('========================================');
            $this->command->info("Votantes creados: 500");
            $this->command->info("Votos registrados: {$votesCreated}");
            $this->command->info("Tiempo de insercion: {$duration} segundos");
            $this->command->info("Votos por segundo: " . round($votesCreated / $duration, 2));
            $this->command->newLine();

            // Mostrar resultados
            $this->command->info('RESULTADOS:');
            $results = Candidate::orderBy('votes_count', 'desc')->get();
            foreach ($results as $candidate) {
                $this->command->info("  {$candidate->name}: {$candidate->votes_count} votos");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error en la prueba: ' . $e->getMessage());
            $this->command->error($e->getTraceAsString());
        }
    }
}
