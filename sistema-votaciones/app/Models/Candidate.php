<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'photo',
        'position',
        'is_blank_vote',
        'is_active',
        'votes_count',
    ];

    protected function casts(): array
    {
        return [
            'is_blank_vote' => 'boolean',
            'is_active' => 'boolean',
            'position' => 'integer',
            'votes_count' => 'integer',
        ];
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function incrementVotes(): void
    {
        $this->increment('votes_count');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    public function hasVotes(): bool
    {
        return $this->votes_count > 0;
    }
}
