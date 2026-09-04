<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class ExpireInactiveSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $lastActivity = $request->session()->get('_last_activity_at');
        $lifetime = (int) config('session.lifetime');

        if (Auth::check() && $lastActivity && Carbon::parse($lastActivity)->addMinutes($lifetime)->isPast()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->guest(route('login'))->with('error', 'Your session expired due to inactivity. Please log in again.');
        }

        $request->session()->put('_last_activity_at', now()->toIso8601String());

        return $next($request);
    }
}