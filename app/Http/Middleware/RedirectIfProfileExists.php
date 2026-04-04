<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RedirectIfProfileExists
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
         $user = Auth::user();

        if ($user && $user->profile) {
            // لو عنده بروفايل، اديه redirection لصفحة تانية
            return redirect('/home')->with('info', 'You already completed your profile!');
        }

        return $next($request);
    }
}
