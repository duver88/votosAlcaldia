<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Admin;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'identificacion' => 'required|string',
            'password' => 'required|string',
        ]);

        $key = 'login-attempts:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'identificacion' => "Demasiados intentos. Intente de nuevo en {$seconds} segundos.",
            ]);
        }

        $identificacion = trim($request->identificacion);
        $isEmail = filter_var($identificacion, FILTER_VALIDATE_EMAIL);

        // Si es email, buscar en admins
        if ($isEmail) {
            return $this->loginAdmin($request, $identificacion, $key);
        }

        // Si no es email, buscar en votantes por cedula
        return $this->loginVoter($request, $identificacion, $key);
    }

    protected function loginAdmin(Request $request, string $email, string $key)
    {
        $admin = Admin::where('email', $email)->first();

        if (!$admin) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages([
                'identificacion' => 'Credenciales incorrectas.',
            ]);
        }

        if (!Hash::check($request->password, $admin->password)) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages([
                'password' => 'Contrasena incorrecta.',
            ]);
        }

        RateLimiter::clear($key);
        Auth::guard('admin')->login($admin);
        $request->session()->regenerate();
        ActivityLog::log('admin_login', "Inicio de sesion de administrador: {$admin->email}", null, $admin->id);

        return redirect()->route('admin.dashboard');
    }

    protected function loginVoter(Request $request, string $cedula, string $key)
    {
        $user = User::where('cedula', $cedula)->first();

        if (!$user) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages([
                'identificacion' => 'Cedula no registrada.',
            ]);
        }

        if ($user->is_blocked) {
            throw ValidationException::withMessages([
                'identificacion' => 'Cuenta bloqueada. Contacte al administrador.',
            ]);
        }

        if (!Hash::check($request->password, $user->password)) {
            $user->incrementFailedAttempts();
            RateLimiter::hit($key, 60);

            if ($user->is_blocked) {
                ActivityLog::log('user_blocked', "Usuario bloqueado por exceder intentos fallidos", $user->cedula);
                throw ValidationException::withMessages([
                    'identificacion' => 'Cuenta bloqueada por exceder intentos fallidos. Contacte al administrador.',
                ]);
            }

            throw ValidationException::withMessages([
                'password' => 'Contrasena incorrecta. Intentos restantes: ' . (5 - $user->failed_attempts),
            ]);
        }

        $user->resetFailedAttempts();
        $user->recordLogin();
        RateLimiter::clear($key);
        Auth::login($user);
        $request->session()->regenerate();
        ActivityLog::log('user_login', "Inicio de sesion exitoso", $user->cedula);

        if ($user->must_change_password) {
            return redirect()->route('change-password');
        }

        return redirect()->route('vote');
    }

    public function logout(Request $request)
    {
        // Logout admin si esta logueado
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            ActivityLog::log('admin_logout', "Cierre de sesion de administrador: {$admin->email}", null, $admin->id);
            Auth::guard('admin')->logout();
        }

        // Logout votante si esta logueado
        if (Auth::check()) {
            $cedula = auth()->user()->cedula;
            ActivityLog::log('user_logout', "Cierre de sesion", $cedula);
            Auth::logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
