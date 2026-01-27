<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\VotingSetting;
use App\Models\ActivityLog;

class CheckVotingSchedule extends Command
{
    protected $signature = 'voting:check-schedule';
    protected $description = 'Check if voting should be opened or closed based on schedule';

    public function handle(): int
    {
        $settings = VotingSetting::current();
        if (!$settings) {
            $this->info('No voting settings found.');
            return 0;
        }
        if ($settings->shouldAutoOpen()) {
            $settings->is_active = true;
            $settings->save();
            ActivityLog::log('voting_auto_open', 'Votacion abierta automaticamente segun programacion');
            $this->info('Voting opened automatically.');
        }
        if ($settings->shouldAutoClose()) {
            $settings->is_active = false;
            $settings->save();
            ActivityLog::log('voting_auto_close', 'Votacion cerrada automaticamente segun programacion');
            $this->info('Voting closed automatically.');
        }
        return 0;
    }
}
