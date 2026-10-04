<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    /**
     * Logs out a user (Availability requirement) whose session has been idle
     * for longer than SESSION_TIMEOUT_MINUTES, protecting unattended sessions
     * in the counselling office.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $timeoutMinutes = (int) config('session.timeout_minutes', 15);
            $lastActivity = $request->session()->get('last_activity_at');

            // Carbon 3's diffInMinutes() returns a signed value (negative when
            // $lastActivity is in the past relative to now), so it must be
            // wrapped in abs() to get elapsed minutes regardless of direction.
            if ($lastActivity && abs(now()->diffInMinutes($lastActivity)) >= $timeoutMinutes) {
                AuditLog::record('session_timeout', 'Session expired due to inactivity', Auth::id());

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                $request->session()->flash('status', 'Your session expired due to inactivity. Please log in again.');

                throw new AuthenticationException('Your session has expired due to inactivity. Please log in again.');
            }

            $request->session()->put('last_activity_at', now());
        }

        return $next($request);
    }
}
