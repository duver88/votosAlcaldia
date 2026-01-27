<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'cedula',
        'password',
        'must_change_password',
        'is_blocked',
        'failed_attempts',
        'has_voted',
        'login_at',
        'voted_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'is_blocked' => 'boolean',
            'has_voted' => 'boolean',
            'login_at' => 'datetime',
            'voted_at' => 'datetime',
        ];
    }

    public function getAuthIdentifierName(): string
    {
        return 'cedula';
    }

    public function incrementFailedAttempts(): void
    {
        $this->failed_attempts++;
        if ($this->failed_attempts >= 5) {
            $this->is_blocked = true;
        }
        $this->save();
    }

    public function resetFailedAttempts(): void
    {
        $this->failed_attempts = 0;
        $this->save();
    }

    public function recordLogin(): void
    {
        $this->login_at = now();
        $this->save();
    }

    public function recordVote(): void
    {
        $this->has_voted = true;
        $this->voted_at = now();
        $this->save();
    }
}
