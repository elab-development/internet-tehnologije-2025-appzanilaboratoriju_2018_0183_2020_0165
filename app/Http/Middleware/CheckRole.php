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


        $uloga = $request->user()->uloge->firstWhere('Naziv', $role);

        if (!$uloga) {
            return response()->json([
                'error' => 'Zabranjen pristup',
                'potrebna_uloga' => $role
            ], 403);
        }

        if (!$request->user()->tokenCan('uloga:' . $uloga->UlogaID)) {
            return response()->json([
                'error' => 'Niste prijavljeni u ovoj ulozi',
                'potrebna_uloga' => $role,
                'poruka' => 'Odjavite se i prijavite ponovo birajući ulogu ' . $role . '.'
            ], 403);
        }

        return $next($request);
    }
}
