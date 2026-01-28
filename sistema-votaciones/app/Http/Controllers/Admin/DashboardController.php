<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Candidate;
use App\Models\Vote;
use App\Models\VotingSetting;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $settings = VotingSetting::current();

        // Auto-cerrar si las fechas expiraron
        if ($settings && $settings->is_active && $settings->shouldAutoClose()) {
            $settings->is_active = false;
            $settings->save();
        }

        $totalVoters = User::count();
        $totalVotes = Vote::count();
        $pendingVoters = User::where('has_voted', false)->count();
        $participation = $totalVoters > 0 ? round(($totalVotes / $totalVoters) * 100, 2) : 0;
        $candidates = Candidate::orderBy('votes_count', 'desc')->get();

        
        $candidatesJson = $candidates->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'votes_count' => $c->votes_count,
            ];
        })->toArray();

        // Analytics: ganador, margen, diferencia
        $winner = $candidates->first();
        $runnerUp = $candidates->count() > 1 ? $candidates->skip(1)->first() : null;
        $margin = ($winner && $runnerUp) ? $winner->votes_count - $runnerUp->votes_count : ($winner ? $winner->votes_count : 0);
        $winnerPct = ($totalVotes > 0 && $winner) ? round(($winner->votes_count / $totalVotes) * 100, 1) : 0;

        return view('admin.dashboard', compact(
            'settings',
            'totalVoters',
            'totalVotes',
            'pendingVoters',
            'participation',
            'candidates',
            'candidatesJson',
            'winner',
            'runnerUp',
            'margin',
            'winnerPct'
        ));
    }

    public function getStats()
    {
        $totalVoters = User::count();
        $totalVotes = Vote::count();
        $pendingVoters = User::where('has_voted', false)->count();
        $participation = $totalVoters > 0 ? round(($totalVotes / $totalVoters) * 100, 2) : 0;
        $candidates = Candidate::orderBy('votes_count', 'desc')->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'votes_count' => $c->votes_count,
            ];
        });
        return response()->json([
            'totalVoters' => $totalVoters,
            'totalVotes' => $totalVotes,
            'pendingVoters' => $pendingVoters,
            'participation' => $participation,
            'candidates' => $candidates,
        ]);
    }
}
