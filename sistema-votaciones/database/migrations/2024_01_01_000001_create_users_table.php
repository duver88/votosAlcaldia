<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('cedula', 20)->unique();
            $table->string('password');
            $table->boolean('must_change_password')->default(true);
            $table->boolean('is_blocked')->default(false);
            $table->tinyInteger('failed_attempts')->default(0);
            $table->boolean('has_voted')->default(false);
            $table->timestamp('login_at')->nullable();
            $table->timestamp('voted_at')->nullable();
            $table->timestamps();
            $table->index('cedula');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
