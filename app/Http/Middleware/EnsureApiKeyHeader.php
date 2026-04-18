<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiKeyHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.frontend.api_key', '');

        // Allow requests when no key is configured.
        if ($expected === '') {
            return $next($request);
        }

        $provided = (string) $request->header('X-API-Key', '');
        if ($provided === '' || ! hash_equals($expected, $provided)) {
            return response()->json([
                'message' => 'Invalid API key.',
            ], 401);
        }

        return $next($request);
    }
}
