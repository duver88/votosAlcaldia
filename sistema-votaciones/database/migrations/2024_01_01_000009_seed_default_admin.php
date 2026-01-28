<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        // Verificar si ya existe el admin
        $exists = DB::table('admins')->where('email', 'oficinatic@bucaramanga.gov.co')->exists();

        if (!$exists) {
            DB::table('admins')->insert([
                'name' => 'Oficina TIC',
                'email' => 'oficinatic@bucaramanga.gov.co',
                'password' => Hash::make('0M2anix0G'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('admins')->where('email', 'oficinatic@bucaramanga.gov.co')->delete();
    }
};
