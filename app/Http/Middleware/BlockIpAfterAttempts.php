<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;

class BlockIpAfterAttempts
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();

        if (Cache::has('blocked_ip_' . $ip)) {
            return response()->json([
                'message' => 'Your IP is blocked. Try later.'
            ], 429);
        }

        return $next($request);
    }
}
