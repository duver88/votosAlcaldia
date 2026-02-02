<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class VotingSetting extends Model
{
    protected $fillable = [
        'start_datetime',
        'end_datetime',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_datetime' => 'datetime',
            'end_datetime' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public static function current(): ?self
    {
        return self::first();
    }

    // Siempre usar hora de Colombia
    private function colombiaTime(): Carbon
    {
        return Carbon::now('America/Bogota');
    }

    public function isVotingOpen(): bool
    {
        if (!$this->is_active) {
            return false;
        }
        $now = $this->colombiaTime();
        if ($this->start_datetime && $now->lt($this->start_datetime)) {
            return false;
        }
        if ($this->end_datetime && $now->gt($this->end_datetime)) {
            return false;
        }
        return true;
    }

    public function shouldAutoOpen(): bool
    {
        if ($this->is_active) {
            return false;
        }
        if (!$this->start_datetime) {
            return false;
        }
        return $this->colombiaTime()->gte($this->start_datetime);
    }

    public function shouldAutoClose(): bool
    {
        if (!$this->is_active) {
            return false;
        }
        if (!$this->end_datetime) {
            return false;
        }
        return $this->colombiaTime()->gte($this->end_datetime);
    }
}
