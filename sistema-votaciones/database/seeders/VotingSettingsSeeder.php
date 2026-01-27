<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VotingSetting;

class VotingSettingsSeeder extends Seeder
{
    public function run(): void
    {
        VotingSetting::create([
            'start_datetime' => null,
            'end_datetime' => null,
            'is_active' => false,
        ]);
    }
}
