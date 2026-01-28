<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VotingSetting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = VotingSetting::current() ?? new VotingSetting();

        // Auto-cerrar si las fechas expiraron
        if ($settings->exists && $settings->is_active && $settings->shouldAutoClose()) {
            $settings->is_active = false;
            $settings->save();
            ActivityLog::log('voting_toggled', 'Votacion cerrada automaticamente (fecha de cierre alcanzada)', null, Auth::guard('admin')->id());
        }

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

        // Si va a abrir, validar que tenga fechas válidas
        if (!$settings->is_active) {
            $now = Carbon::now();

            if (!$settings->start_datetime || !$settings->end_datetime) {
                return back()->with('error', 'Debe configurar la fecha de apertura y cierre antes de abrir la votacion.');
            }

            if ($settings->end_datetime->lte($now)) {
                return back()->with('error', 'La fecha de cierre ya paso. Actualice las fechas antes de abrir la votacion.');
            }

            if ($settings->start_datetime->gte($settings->end_datetime)) {
                return back()->with('error', 'La fecha de apertura debe ser anterior a la fecha de cierre.');
            }
        }

        $settings->is_active = !$settings->is_active;
        $settings->save();
        $status = $settings->is_active ? 'abierta' : 'cerrada';
        ActivityLog::log('voting_toggled', "Votacion {$status} manualmente", null, Auth::guard('admin')->id());
        return back()->with('success', "Votacion {$status} exitosamente.");
    }
}
