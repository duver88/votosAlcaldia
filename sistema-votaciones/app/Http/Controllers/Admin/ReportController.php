<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\User;
use App\Models\Vote;

class ReportController extends Controller
{
    public function index()
    {
        $totalVotes = Vote::count();
        $totalVoters = User::count();
        $participation = $totalVoters > 0 ? round(($totalVotes / $totalVoters) * 100, 2) : 0;

        $candidates = Candidate::active()
            ->ordered()
            ->get()
            ->map(function ($candidate) use ($totalVotes) {
                $candidate->percentage = $totalVotes > 0
                    ? round(($candidate->votes_count / $totalVotes) * 100, 2)
                    : 0;
                return $candidate;
            });

        return view('admin.report', compact('candidates', 'totalVotes', 'totalVoters', 'participation'));
    }
}
