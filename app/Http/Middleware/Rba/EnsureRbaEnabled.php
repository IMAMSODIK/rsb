<?php

namespace App\Http\Middleware\Rba;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRbaEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('rba.enabled', true)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fitur RBA sedang dinonaktifkan. Set RBA_ENABLED=true di .env.',
                ], 404);
            }

            abort(404, 'Fitur RBA sedang dinonaktifkan. Set RBA_ENABLED=true di .env.');
        }

        return $next($request);
    }
}
