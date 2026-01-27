<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VotingSetting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = VotingSetting::current() ?? new VotingSetting();
        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'start_datetime' => 'nullable|date',
            'end_datetime' => 'nullable|date|after:start_datetime',
        ]);
        $settings = VotingSetting::current();
        if (!$settings) {
            $settings = new VotingSetting();
        }
        $settings->start_datetime = $request->start_datetime;
        $settings->end_datetime = $request->end_datetime;
        $settings->save();
        ActivityLog::log('settings_updated', "Configuracion de votacion actualizada", null, Auth::guard('admin')->id());
        return back()->with('success', 'Configuracion actualizada exitosamente.');
    }

    public function toggleVoting()
    {
        $settings = VotingSetting::current();
        if (!$settings) {
            $settings = VotingSetting::create(['is_active' => false]);
        }
        $settings->is_active = !$settings->is_active;
        $settings->save();
        $status = $settings->is_active ? 'abierta' : 'cerrada';
        ActivityLog::log('voting_toggled', "Votacion {$status} manualmente", null, Auth::guard('admin')->id());
        return back()->with('success', "Votacion {$status} exitosamente.");
    }
}
