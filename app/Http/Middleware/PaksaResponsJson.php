<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Semua request ke /api dianggap meminta JSON, walau klien lupa mengirim header
 * "Accept: application/json". Error (401, 404, 422, dst.) jadi selalu berupa JSON.
 */
class PaksaResponsJson
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
