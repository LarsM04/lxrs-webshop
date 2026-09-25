<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Laat een verzoek alleen door als de header X-API-Key de juiste sleutel bevat.
 *
 * De sleutel staat in .env als LXRS_API_KEY. Staat daar niets, dan gaat alles
 * dicht: liever niemand dan iedereen.
 */
class EnsureApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $sleutel = (string) config('services.lxrs_api.key');
        $meegestuurd = (string) $request->header('X-API-Key');

        // hash_equals in plaats van ===, zodat de vergelijking altijd even lang
        // duurt en je de sleutel niet letter voor letter kunt raden.
        if ($sleutel === '' || ! hash_equals($sleutel, $meegestuurd)) {
            return response()->json(['message' => 'Ongeldige of ontbrekende API-sleutel.'], 401);
        }

        return $next($request);
    }
}
