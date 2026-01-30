<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = auth()->guard('admin')->user();

        if (!$admin || !$admin->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
            return redirect()->route('admin.dashboard')->with('error', 'No tiene permisos para acceder a esta seccion.');
        }

        return $next($request);
    }
}
