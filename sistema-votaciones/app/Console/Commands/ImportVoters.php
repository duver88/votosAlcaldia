<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\ActivityLog;

class ImportVoters extends Command
{
    protected $signature = 'voters:import {file : Path to CSV file}';
    protected $description = 'Import voters from a CSV file (format: cedula,password)';

    public function handle(): int
    {
        $filePath = $this->argument('file');
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            $this->error("Could not open file: {$filePath}");
            return 1;
        }
        $imported = 0;
        $errors = 0;
        $lineNumber = 0;
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $lineNumber++;
            if (count($data) < 2) {
                $this->warn("Line {$lineNumber}: Invalid format, skipping.");
                $errors++;
                continue;
            }
            $cedula = trim($data[0]);
            $password = trim($data[1]);
            if (empty($cedula) || empty($password)) {
                $this->warn("Line {$lineNumber}: Empty cedula or password, skipping.");
                $errors++;
                continue;
            }
            if (User::where('cedula', $cedula)->exists()) {
                $this->warn("Line {$lineNumber}: Cedula {$cedula} already exists, skipping.");
                $errors++;
                continue;
            }
            User::create([
                'cedula' => $cedula,
                'password' => $password,
                'must_change_password' => true,
            ]);
            $imported++;
        }
        fclose($handle);
        ActivityLog::log('voters_imported_cli', "Voters imported via CLI: {$imported}");
        $this->info("Import completed. Imported: {$imported}, Errors: {$errors}");
        return 0;
    }
}
