<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequirePasswordChange
{
    /**
     * Holds a user whose password was issued by the Admin at the change-password
     * screen. Until they choose their own, the Admin knows the password to an
     * account that opens counselling records, so nothing else is reachable.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->must_change_password && ! $this->isAllowed($request)) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Your password must be changed before continuing.'], 403)
                : redirect()->route('password.change');
        }

        return $next($request);
    }

    /**
     * The change-password screen, either route that actually changes a password,
     * and signing out. Anything else would let the account be used as it is.
     */
    private function isAllowed(Request $request): bool
    {
        return $request->routeIs(
            'password.change',
            'password.change.update',
            'password.update',
            'logout',
        );
    }
}
