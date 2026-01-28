<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\User;
use App\Models\Vote;
use App\Models\VotingSetting;
use App\Models\ActivityLog;
use App\Events\VoteCast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoteController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user->has_voted) {
            return view('vote.already-voted', [
                'votedAt' => $user->voted_at,
            ]);
        }
        $settings = VotingSetting::current();
        if (!$settings || !$settings->isVotingOpen()) {
            return view('vote.closed');
        }
        $candidates = Candidate::active()->ordered()->get();
        return view('vote.index', compact('candidates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'candidate_id' => 'required|exists:candidates,id',
        ]);
        $user = auth()->user();
        if ($user->has_voted) {
            return redirect()->route('vote')->with('error', 'Usted ya emitio su voto.');
        }
        $settings = VotingSetting::current();
        if (!$settings || !$settings->isVotingOpen()) {
            return redirect()->route('vote')->with('error', 'La votacion no esta disponible.');
        }
        $candidate = Candidate::findOrFail($request->candidate_id);
        if (!$candidate->is_active) {
            return redirect()->route('vote')->with('error', 'Candidato no disponible.');
        }
        try {
            DB::transaction(function () use ($user, $candidate) {
                // Bloqueo pesimista: evita que dos requests simultáneos pasen el check
                $lockedUser = User::lockForUpdate()->find($user->id);

                if ($lockedUser->has_voted) {
                    throw new \RuntimeException('ALREADY_VOTED');
                }

                Vote::create([
                    'candidate_id' => $candidate->id,
                    'voted_at' => now(),
                    'created_at' => now(),
                ]);

                $candidate->increment('votes_count');

                $lockedUser->has_voted = true;
                $lockedUser->voted_at = now();
                $lockedUser->save();
            });

            // Regenerar session para invalidar tokens anteriores
            $request->session()->regenerate();

            ActivityLog::log('vote_cast', "Voto emitido exitosamente", $user->cedula);

           
            try {
                broadcast(new VoteCast())->toOthers();
            } catch (\Exception $e) {
                \Log::warning('Error broadcasting vote: ' . $e->getMessage());
            }

            return redirect()->route('vote.confirmation');
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'ALREADY_VOTED') {
                return redirect()->route('vote')->with('error', 'Usted ya emitio su voto.');
            }
            \Log::error('Error al procesar voto: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ':' . $e->getLine());
            return redirect()->route('vote')->with('error', 'Error al procesar el voto. Intente de nuevo.');
        } catch (\Exception $e) {
            \Log::error('Error al procesar voto: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ':' . $e->getLine());
            return redirect()->route('vote')->with('error', 'Error al procesar el voto. Intente de nuevo.');
        }
    }

    public function confirmation()
    {
        $user = auth()->user();
        if (!$user->has_voted) {
            return redirect()->route('vote');
        }
        return view('vote.confirmation', [
            'votedAt' => $user->voted_at,
        ]);
    }

    public function closed()
    {
        return view('vote.closed');
    }
}
