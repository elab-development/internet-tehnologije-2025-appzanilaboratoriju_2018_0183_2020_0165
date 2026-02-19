<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
public function handle(Request $request, Closure $next, string $role): Response
    {
        
        if (!$request->user()) {
            return response()->json(['message' => 'Niste prijavljeni.'], 401);
        }

        // KLJUČNA LINIJA:
        // 'uloge' -> naziv metode u tvom modelu Korisnik
        // 'naziv_uloge' -> naziv kolone u tvojoj tabeli uloga
        $imaUlogu = $request->user()->uloge->contains('Naziv', $role);

        if (!$imaUlogu) {
            return response()->json([
                'error' => 'Zabranjen pristup',
                'potrebna_uloga' => $role
            ], 403);
        }

        return $next($request);
    }
}
