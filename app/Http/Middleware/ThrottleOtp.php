<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ThrottleOtp
{
    protected $maxAttempts = 5;


    protected $decayMinutes = 1;
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        dd($request->ip());
        $key = 'otp_attempts_' . $request->ip();

        $attempts = cache()->get($key, 0);

        if ($attempts >= $this->maxAttempts) {
            return back()->withErrors([
                'code' => "Too many attempts. Please try again in {$this->decayMinutes} minute(s)."
            ]);
        }

        cache()->increment($key);
        cache()->put($key, cache()->get($key), now()->addMinutes($this->decayMinutes));

        $response = $next($request);

        return $response;
    }
}
