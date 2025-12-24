<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use function json_decode;

class IdempotencyKeysMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-Idempotency-Key');

        if (!$key) {
            return $next($request);
        }

        if ($response = Cache::get("idempotency:$key")) {
            return response()->json(json_decode($response, true), 200);
        }

        $response = $next($request);

        if ($response->isSuccessful()) {
            Cache::put("idempotency:$key", $response->getContent(), now()->addMinutes(5));
        }

        return $response;
    }
}
