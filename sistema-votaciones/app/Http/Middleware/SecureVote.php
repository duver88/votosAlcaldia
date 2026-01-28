<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecureVote
{
    public function handle(Request $request, Closure $next)
    {
        // Rechazar requests AJAX/API directos (solo formularios HTML)
        if ($request->expectsJson()) {
            abort(403, 'Acceso no permitido.');
        }

        // Validar que el referer sea del mismo host
        $referer = $request->headers->get('referer');
        if ($referer) {
            $refererHost = parse_url($referer, PHP_URL_HOST);
            $appHost = $request->getHost();
            if ($refererHost !== $appHost) {
                abort(403, 'Acceso no permitido.');
            }
        }

        return $next($request);
    }
}
