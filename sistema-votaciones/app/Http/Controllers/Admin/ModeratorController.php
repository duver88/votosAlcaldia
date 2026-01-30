<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ModeratorController extends Controller
{
    public function index()
    {
        $moderators = Admin::where('role', 'moderator')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.moderators.index', compact('moderators'));
    }

    public function create()
    {
        return view('admin.moderators.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
        ]);

        $password = Str::random(10);

        Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $password,
            'role' => 'moderator',
        ]);

        ActivityLog::log('moderator_created', "Moderador creado: {$request->email}", null, Auth::guard('admin')->id());

        return redirect()->route('admin.moderators.index')
            ->with('success', "Moderador creado exitosamente. Contrasena temporal: {$password}");
    }

    public function destroy(Admin $moderator)
    {
        if ($moderator->role !== 'moderator') {
            return back()->with('error', 'No se puede eliminar un administrador desde aqui.');
        }

        $email = $moderator->email;
        $moderator->delete();

        ActivityLog::log('moderator_deleted', "Moderador eliminado: {$email}", null, Auth::guard('admin')->id());

        return redirect()->route('admin.moderators.index')
            ->with('success', 'Moderador eliminado exitosamente.');
    }

    public function resetPassword(Admin $moderator)
    {
        if ($moderator->role !== 'moderator') {
            return back()->with('error', 'No se puede resetear la contrasena de un administrador desde aqui.');
        }

        $newPassword = Str::random(10);
        $moderator->password = $newPassword;
        $moderator->save();

        ActivityLog::log('moderator_password_reset', "Contrasena reseteada para moderador: {$moderator->email}", null, Auth::guard('admin')->id());

        return back()->with('success', "Contrasena reseteada. Nueva contrasena: {$newPassword}");
    }
}
