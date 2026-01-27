<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChangePasswordController extends Controller
{
    public function showChangeForm()
    {
        return view('auth.change-password');
    }

    public function change(Request $request)
    {
        $request->validate([
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->numbers(),
            ],
        ], [
            'password.min' => 'La contrasena debe tener al menos 8 caracteres.',
            'password.mixed' => 'La contrasena debe tener al menos una mayuscula y una minuscula.',
            'password.numbers' => 'La contrasena debe tener al menos un numero.',
            'password.confirmed' => 'Las contrasenas no coinciden.',
        ]);
        $user = auth()->user();
        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'La nueva contrasena no puede ser igual a la anterior.']);
        }
        $user->password = $request->password;
        $user->must_change_password = false;
        $user->save();
        ActivityLog::log('password_changed', "Cambio de contrasena realizado", $user->cedula);
        return redirect()->route('vote')->with('success', 'Contrasena actualizada correctamente.');
    }
}
