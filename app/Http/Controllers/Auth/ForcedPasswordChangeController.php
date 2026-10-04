<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * The change a user must make after the Admin issues them a password.
 */
class ForcedPasswordChangeController extends Controller
{
    public function edit(Request $request): View|RedirectResponse
    {
        // Reaching this page with nothing to change means the user typed the
        // address themselves; send them on rather than showing a dead form.
        return $request->user()->must_change_password
            ? view('auth.change-password')
            : redirect()->route('dashboard');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed', 'different:current_password'],
        ]);

        $user = $request->user();

        // must_change_password sits outside $fillable so no form can clear it.
        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
        ])->save();

        // The password that got them in here is now spent, so the session that
        // carried it starts again.
        $request->session()->regenerate();

        AuditLog::record('password_changed', 'Chose a new password after an administrator issued one', $user->id);

        return redirect()->route('dashboard')
            ->with('status', 'Your password has been changed. Only you know it now.');
    }
}
