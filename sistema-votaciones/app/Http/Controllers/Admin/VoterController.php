<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class VoterController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();
        if ($request->filter === 'blocked') {
            $query->where('is_blocked', true);
        } elseif ($request->filter === 'voted') {
            $query->where('has_voted', true);
        } elseif ($request->filter === 'pending') {
            $query->where('has_voted', false);
        }
        if ($request->search) {
            $query->where('cedula', 'like', '%' . $request->search . '%');
        }
        $voters = $query->orderBy('cedula')->paginate(20);
        return view('admin.voters.index', compact('voters'));
    }

    public function create()
    {
        return view('admin.voters.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'cedula' => 'required|string|max:20|unique:users,cedula|regex:/^[0-9]+$/',
            'password' => 'required|string|min:8',
        ], [
            'cedula.regex' => 'La cedula solo debe contener numeros.',
        ]);
        $user = User::create([
            'cedula' => $request->cedula,
            'password' => $request->password,
            'must_change_password' => true,
        ]);
        ActivityLog::log('voter_created', "Votante creado: {$user->cedula}", null, Auth::guard('admin')->id());
        return redirect()->route('admin.voters.index')->with('success', 'Votante creado exitosamente.');
    }

    public function unblock(User $voter)
    {
        $voter->is_blocked = false;
        $voter->failed_attempts = 0;
        $voter->save();
        ActivityLog::log('voter_unblocked', "Votante desbloqueado: {$voter->cedula}", null, Auth::guard('admin')->id());
        return back()->with('success', 'Votante desbloqueado exitosamente.');
    }

    public function resetPassword(User $voter)
    {
        $newPassword = Str::random(8);
        $voter->password = $newPassword;
        $voter->must_change_password = true;
        $voter->save();
        ActivityLog::log('voter_password_reset', "Contrasena reseteada para: {$voter->cedula}", null, Auth::guard('admin')->id());
        return back()->with('success', "Contrasena reseteada. Nueva contrasena: {$newPassword}");
    }

    public function import(Request $request)
    {
        // Aumentar tiempo limite para importaciones grandes
        set_time_limit(300); // 5 minutos

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');
        $imported = 0;
        $errors = [];
        $lineNumber = 0;
        $batch = [];
        $batchSize = 100;

        // Obtener todas las cedulas existentes para verificacion rapida
        $existingCedulas = User::pluck('cedula')->flip()->toArray();

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $lineNumber++;
            if (count($data) < 2) {
                $errors[] = "Linea {$lineNumber}: formato invalido";
                continue;
            }
            $cedula = trim($data[0]);
            $password = trim($data[1]);
            if (empty($cedula) || empty($password)) {
                $errors[] = "Linea {$lineNumber}: cedula o contrasena vacia";
                continue;
            }
            if (isset($existingCedulas[$cedula])) {
                $errors[] = "Linea {$lineNumber}: cedula {$cedula} ya existe";
                continue;
            }

            // Agregar a batch
            $batch[] = [
                'cedula' => $cedula,
                'password' => Hash::make($password),
                'must_change_password' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $existingCedulas[$cedula] = true; // Marcar como existente
            $imported++;

            // Insertar en lotes
            if (count($batch) >= $batchSize) {
                DB::table('users')->insert($batch);
                $batch = [];
            }
        }

        // Insertar registros restantes
        if (count($batch) > 0) {
            DB::table('users')->insert($batch);
        }

        fclose($handle);
        ActivityLog::log('voters_imported', "Votantes importados: {$imported}", null, Auth::guard('admin')->id());
        $message = "Se importaron {$imported} votantes.";
        if (count($errors) > 0) {
            $message .= " Errores: " . implode(', ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= "... y " . (count($errors) - 5) . " mas.";
            }
        }
        return back()->with('success', $message);
    }

    public function showImport()
    {
        return view('admin.voters.import');
    }

    public function show(User $voter)
    {
        $logs = ActivityLog::where('user_cedula', $voter->cedula)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'action' => $log->action,
                    'description' => $log->description,
                    'ip' => $log->ip_address,
                    'date' => $log->created_at->format('d/m/Y H:i:s'),
                ];
            });

        return response()->json([
            'cedula' => $voter->cedula,
            'login_at' => $voter->login_at?->format('d/m/Y H:i:s'),
            'voted_at' => $voter->voted_at?->format('d/m/Y H:i:s'),
            'is_blocked' => $voter->is_blocked,
            'has_voted' => $voter->has_voted,
            'must_change_password' => $voter->must_change_password,
            'failed_attempts' => $voter->failed_attempts,
            'logs' => $logs,
        ]);
    }

    public function destroy(User $voter)
    {
        if ($voter->has_voted) {
            return back()->with('error', 'No se puede eliminar un votante que ya emitio su voto.');
        }
        $cedula = $voter->cedula;
        $voter->delete();
        ActivityLog::log('voter_deleted', "Votante eliminado: {$cedula}", null, Auth::guard('admin')->id());
        return back()->with('success', 'Votante eliminado exitosamente.');
    }
}
