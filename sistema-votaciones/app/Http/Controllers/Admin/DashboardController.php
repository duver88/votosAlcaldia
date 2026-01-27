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

        return view('admin.dashboard', compact(
            'settings',
            'totalVoters',
            'totalVotes',
            'pendingVoters',
            'participation',
            'candidates',
            'candidatesJson'
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
