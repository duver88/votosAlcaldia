<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\VotingSetting;

class EnsureVotingOpen
{
    public function handle(Request $request, Closure $next): Response
    {
        $settings = VotingSetting::current();
        if (!$settings || !$settings->isVotingOpen()) {
            return redirect()->route('voting.closed');
        }
        return $next($request);
    }
}
