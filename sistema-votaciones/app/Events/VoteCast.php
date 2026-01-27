<?php

namespace App\Events;

use App\Models\Candidate;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoteCast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $candidates;

    public function __construct()
    {
        $this->candidates = Candidate::orderBy('votes_count', 'desc')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'votes_count' => $c->votes_count,
            ])
            ->toArray();
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('voting-results'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'vote.cast';
    }
}
