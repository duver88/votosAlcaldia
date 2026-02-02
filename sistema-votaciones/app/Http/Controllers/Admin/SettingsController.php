<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VotingSetting;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Candidate;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = VotingSetting::current() ?? new VotingSetting();

        // Hora de Colombia
        $colombiaTime = Carbon::now('America/Bogota');

        // Auto-abrir si llego la hora de apertura
        if ($settings->exists && !$settings->is_active && $settings->shouldAutoOpen()) {
            // Verificar que no haya pasado la fecha de cierre
            if (!$settings->end_datetime || $colombiaTime->lt($settings->end_datetime)) {
                $settings->is_active = true;
                $settings->save();
                ActivityLog::log('voting_toggled', 'Votacion abierta automaticamente (fecha de apertura alcanzada)', null, Auth::guard('admin')->id());
            }
        }

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

    /**
     * Exportar backup completo en CSV
     */
    public function exportBackup()
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $fileName = "backup_votacion_{$timestamp}.csv";

        // Generar CSV combinado
        $csv = "";

        // Seccion: Resumen
        $csv .= "=== RESUMEN GENERAL ===\n";
        $csv .= $this->generateSummaryCSV();
        $csv .= "\n\n";

        // Seccion: Resultados
        $csv .= "=== RESULTADOS DE VOTACION ===\n";
        $csv .= $this->generateResultsCSV();
        $csv .= "\n\n";

        // Seccion: Candidatos
        $csv .= "=== CANDIDATOS ===\n";
        $csv .= $this->generateCandidatesCSV();
        $csv .= "\n\n";

        // Seccion: Votantes
        $csv .= "=== VOTANTES ===\n";
        $csv .= $this->generateVotersCSV();

        ActivityLog::log('backup_exported', 'Backup de datos exportado', null, Auth::guard('admin')->id());

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * Reset completo del sistema (mantiene admins)
     */
    public function reset(Request $request)
    {
        $request->validate([
            'confirm_text' => 'required|in:RESETEAR'
        ], [
            'confirm_text.in' => 'Debe escribir RESETEAR para confirmar.'
        ]);

        try {
            // Guardar conteos para el log
            $votersCount = User::count();
            $candidatesCount = Candidate::count();
            $votesCount = Vote::count();

            // Deshabilitar verificacion de claves foraneas
            Schema::disableForeignKeyConstraints();

            // Eliminar votos
            Vote::truncate();

            // Eliminar votantes
            User::truncate();

            // Eliminar candidatos
            Candidate::truncate();

            // Limpiar logs de actividad
            ActivityLog::truncate();

            // Rehabilitar verificacion de claves foraneas
            Schema::enableForeignKeyConstraints();

            // Resetear configuracion de votacion
            $settings = VotingSetting::current();
            if ($settings) {
                $settings->is_active = false;
                $settings->start_datetime = null;
                $settings->end_datetime = null;
                $settings->save();
            }

            // Registrar el reset
            ActivityLog::log(
                'system_reset',
                "Sistema reseteado. Eliminados: {$votersCount} votantes, {$candidatesCount} candidatos, {$votesCount} votos.",
                null,
                Auth::guard('admin')->id()
            );

            return back()->with('success', 'Sistema reseteado exitosamente. Se eliminaron todos los votantes, candidatos y votos.');
        } catch (\Exception $e) {
            // Asegurar que las FK se reactiven en caso de error
            Schema::enableForeignKeyConstraints();
            return back()->with('error', 'Error al resetear el sistema: ' . $e->getMessage());
        }
    }

    private function generateVotersCSV(): string
    {
        $voters = User::orderBy('id')->get();
        $csv = "ID,Cedula,Nombre,Voto Emitido,Fecha Voto,Bloqueado,Intentos Fallidos,Creado\n";

        foreach ($voters as $voter) {
            $csv .= sprintf(
                "%d,%s,\"%s\",%s,%s,%s,%d,%s\n",
                $voter->id,
                $voter->cedula,
                str_replace('"', '""', $voter->name),
                $voter->has_voted ? 'Si' : 'No',
                $voter->voted_at?->format('Y-m-d H:i:s') ?? '',
                $voter->is_blocked ? 'Si' : 'No',
                $voter->failed_attempts ?? 0,
                $voter->created_at->format('Y-m-d H:i:s')
            );
        }

        return $csv;
    }

    private function generateCandidatesCSV(): string
    {
        $candidates = Candidate::orderBy('position')->get();
        $csv = "ID,Nombre,Votos,Activo,Voto en Blanco,Posicion,Creado\n";

        foreach ($candidates as $candidate) {
            $csv .= sprintf(
                "%d,\"%s\",%d,%s,%s,%d,%s\n",
                $candidate->id,
                str_replace('"', '""', $candidate->name),
                $candidate->votes_count,
                $candidate->is_active ? 'Si' : 'No',
                $candidate->is_blank_vote ? 'Si' : 'No',
                $candidate->position,
                $candidate->created_at->format('Y-m-d H:i:s')
            );
        }

        return $csv;
    }

    private function generateResultsCSV(): string
    {
        $candidates = Candidate::where('is_active', true)->orderByDesc('votes_count')->get();
        $totalVotes = $candidates->sum('votes_count');

        $csv = "Posicion,Candidato,Votos,Porcentaje\n";
        $position = 1;

        foreach ($candidates as $candidate) {
            $percentage = $totalVotes > 0 ? round(($candidate->votes_count / $totalVotes) * 100, 2) : 0;
            $csv .= sprintf(
                "%d,\"%s\",%d,%.2f%%\n",
                $position++,
                str_replace('"', '""', $candidate->name),
                $candidate->votes_count,
                $percentage
            );
        }

        return $csv;
    }

    private function generateSummaryCSV(): string
    {
        $totalVoters = User::count();
        $votedCount = User::where('has_voted', true)->count();
        $pendingCount = $totalVoters - $votedCount;
        $participation = $totalVoters > 0 ? round(($votedCount / $totalVoters) * 100, 2) : 0;
        $totalVotes = Vote::count();
        $candidatesCount = Candidate::where('is_active', true)->count();
        $settings = VotingSetting::current();

        $csv = "Metrica,Valor\n";
        $csv .= "Fecha de Exportacion," . now()->format('Y-m-d H:i:s') . "\n";
        $csv .= "Total Votantes Registrados,{$totalVoters}\n";
        $csv .= "Votos Emitidos,{$votedCount}\n";
        $csv .= "Abstinencia,{$pendingCount}\n";
        $csv .= "Participacion,{$participation}%\n";
        $csv .= "Total Votos en Sistema,{$totalVotes}\n";
        $csv .= "Candidatos Activos,{$candidatesCount}\n";
        $csv .= "Estado Votacion," . ($settings?->is_active ? 'Abierta' : 'Cerrada') . "\n";
        $csv .= "Fecha Apertura," . ($settings?->start_datetime?->format('Y-m-d H:i:s') ?? 'No configurada') . "\n";
        $csv .= "Fecha Cierre," . ($settings?->end_datetime?->format('Y-m-d H:i:s') ?? 'No configurado') . "\n";

        return $csv;
    }
}
