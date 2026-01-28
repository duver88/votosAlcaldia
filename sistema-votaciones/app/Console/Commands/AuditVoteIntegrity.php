<?php

namespace App\Console\Commands;

use App\Models\Candidate;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Console\Command;

class AuditVoteIntegrity extends Command
{
    protected $signature = 'votes:audit';
    protected $description = 'Auditar integridad de votos vs usuarios y detectar inconsistencias';

    public function handle(): int
    {
        $this->info('=== Auditoria de Integridad de Votos ===');
        $this->newLine();

        $totalUsers = User::count();
        $usersVoted = User::where('has_voted', true)->count();
        $totalVoteRows = Vote::count();
        $sumCandidateVotes = (int) Candidate::sum('votes_count');

        $this->table(
            ['Metrica', 'Valor'],
            [
                ['Total usuarios', $totalUsers],
                ['Usuarios con has_voted=true', $usersVoted],
                ['Filas en tabla votes', $totalVoteRows],
                ['Suma candidates.votes_count', $sumCandidateVotes],
            ]
        );

        $errors = 0;

        // Check 1: votes rows vs users
        if ($totalVoteRows > $totalUsers) {
            $this->error("CRITICO: Hay mas votos ({$totalVoteRows}) que usuarios ({$totalUsers})");
            $errors++;
        } else {
            $this->info("OK: votes ({$totalVoteRows}) <= users ({$totalUsers})");
        }

        // Check 2: votes rows vs users who voted
        if ($totalVoteRows !== $usersVoted) {
            $this->warn("ALERTA: votes ({$totalVoteRows}) != users con has_voted ({$usersVoted})");
            $errors++;
        } else {
            $this->info("OK: votes ({$totalVoteRows}) == users con has_voted ({$usersVoted})");
        }

        // Check 3: sum of candidate votes vs vote rows
        if ($sumCandidateVotes !== $totalVoteRows) {
            $this->error("CRITICO: sum(candidates.votes_count) ({$sumCandidateVotes}) != count(votes) ({$totalVoteRows})");
            $errors++;
        } else {
            $this->info("OK: sum(candidates.votes_count) ({$sumCandidateVotes}) == count(votes) ({$totalVoteRows})");
        }

        // Check 4: each candidate's votes_count vs actual vote rows
        $candidates = Candidate::all();
        foreach ($candidates as $c) {
            $actualVotes = Vote::where('candidate_id', $c->id)->count();
            if ($c->votes_count !== $actualVotes) {
                $this->error("CRITICO: Candidato '{$c->name}' tiene votes_count={$c->votes_count} pero hay {$actualVotes} filas en votes");
                $errors++;
            }
        }

        $this->newLine();
        if ($errors === 0) {
            $this->info('Auditoria completada: Sin problemas de integridad.');
            return Command::SUCCESS;
        }

        $this->error("Auditoria completada: {$errors} problema(s) detectado(s).");
        return Command::FAILURE;
    }
}
