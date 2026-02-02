<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CandidateController extends Controller
{
    public function index()
    {
        $candidates = Candidate::ordered()->get();
        return view('admin.candidates.index', compact('candidates'));
    }

    public function create()
    {
        return view('admin.candidates.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20480',
        ]);
        $data = ['name' => $request->name];
        $data['position'] = Candidate::max('position') + 1;
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('candidates', 'public');
        }
        $candidate = Candidate::create($data);
        ActivityLog::log('candidate_created', "Candidato creado: {$candidate->name}", null, Auth::guard('admin')->id());
        return redirect()->route('admin.candidates.index')->with('success', 'Candidato creado exitosamente.');
    }

    public function edit(Candidate $candidate)
    {
        return view('admin.candidates.edit', compact('candidate'));
    }

    public function update(Request $request, Candidate $candidate)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20480',
        ]);
        $data = ['name' => $request->name];
        if ($request->hasFile('photo')) {
            if ($candidate->photo) {
                Storage::disk('public')->delete($candidate->photo);
            }
            $data['photo'] = $request->file('photo')->store('candidates', 'public');
        }
        $candidate->update($data);
        ActivityLog::log('candidate_updated', "Candidato actualizado: {$candidate->name}", null, Auth::guard('admin')->id());
        return redirect()->route('admin.candidates.index')->with('success', 'Candidato actualizado exitosamente.');
    }

    public function destroy(Candidate $candidate)
    {
        if ($candidate->hasVotes()) {
            return back()->with('error', 'No se puede eliminar un candidato que tiene votos.');
        }
        if ($candidate->photo) {
            Storage::disk('public')->delete($candidate->photo);
        }
        $name = $candidate->name;
        $candidate->delete();
        ActivityLog::log('candidate_deleted', "Candidato eliminado: {$name}", null, Auth::guard('admin')->id());
        return redirect()->route('admin.candidates.index')->with('success', 'Candidato eliminado exitosamente.');
    }

    public function toggleActive(Candidate $candidate)
    {
        $candidate->is_active = !$candidate->is_active;
        $candidate->save();
        $status = $candidate->is_active ? 'activado' : 'desactivado';
        ActivityLog::log('candidate_toggled', "Candidato {$status}: {$candidate->name}", null, Auth::guard('admin')->id());
        return back()->with('success', "Candidato {$status} exitosamente.");
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:candidates,id',
        ]);
        foreach ($request->order as $position => $id) {
            Candidate::where('id', $id)->update(['position' => $position]);
        }
        return response()->json(['success' => true]);
    }
}
