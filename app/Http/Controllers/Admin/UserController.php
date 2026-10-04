<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Support\SchoolClasses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->paginate(15);

        return view('admin.users.index', ['users' => $users]);
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:admin,counsellor,teacher'],
            'class' => ['nullable', Rule::in(SchoolClasses::all())],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            // Only a teacher is tied to a class; the field is ignored for other roles.
            'class' => $validated['role'] === User::ROLE_TEACHER ? ($validated['class'] ?? null) : null,
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        // The Admin typed this password, so the holder replaces it at first use.
        $user->forceFill(['must_change_password' => true])->save();

        AuditLog::record('user_created', "Created {$user->role} account for {$user->email}");

        return redirect()->route('admin.users.index')->with('status', 'User account created.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', ['user' => $user]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'in:admin,counsellor,teacher'],
            'class' => ['nullable', Rule::in(SchoolClasses::all())],
        ]);

        // An Admin changing their own role would lock themselves out of user
        // management, and demoting the last Admin would lock everyone out.
        if ($validated['role'] !== $user->role) {
            abort_if($user->id === auth()->id(), 403, 'You cannot change your own role.');

            // Only an active Admin keeps the school able to manage accounts, so
            // demoting an already deactivated one is harmless.
            abort_if(
                $user->isAdmin() && $user->is_active
                    && User::where('role', User::ROLE_ADMIN)->where('is_active', true)->count() <= 1,
                403,
                'This is the only active administrator account, so its role cannot be changed.'
            );
        }

        $validated['class'] = $validated['role'] === User::ROLE_TEACHER ? ($validated['class'] ?? null) : null;
        $roleChanged = $validated['role'] !== $user->role ? " (role {$user->role} \u{2192} {$validated['role']})" : '';

        $user->update($validated);

        AuditLog::record('user_updated', "Updated account for {$user->email}{$roleChanged}");

        return redirect()->route('admin.users.index')->with('status', 'User account updated.');
    }

    /**
     * Toggle active/deactivated instead of hard-deleting, preserving record history.
     */
    public function toggleActive(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'You cannot deactivate your own account.');

        $user->update(['is_active' => ! $user->is_active]);

        AuditLog::record(
            $user->is_active ? 'user_activated' : 'user_deactivated',
            "{$user->email} was ".($user->is_active ? 'activated' : 'deactivated')
        );

        return back()->with('status', 'User account '.($user->is_active ? 'activated.' : 'deactivated.'));
    }

    /**
     * FR: Reset Staff Password — SafeNote sends no reset links by email, so a
     * member of staff who is locked out is identified in person and given a new
     * password here. The account is unlocked and every session still signed in
     * as that user is ended, in case the lockout followed a compromise.
     */
    public function resetPassword(Request $request, User $user)
    {
        abort_if(
            $user->id === auth()->id(),
            403,
            'Change your own password from your profile, so it is never known to anyone else.'
        );

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // password is not mass assignable alongside the lockout counters, which
        // are deliberately outside $fillable, so all three are forced together.
        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'must_change_password' => true,
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ])->save();

        $endedSessions = $this->endSessionsFor($user);

        AuditLog::record(
            'password_reset_by_admin',
            "Issued a new password for {$user->email}"
                .($endedSessions ? " and ended {$endedSessions} active session(s)" : '')
        );

        return back()->with('status', "A new password was issued for {$user->name}. Give it to them directly — SafeNote will ask them to choose their own before they can go any further.");
    }

    /**
     * Signs the user out everywhere. Only the database session driver keeps
     * sessions where they can be reached; on any other driver this is a no-op
     * and the password change alone takes effect at the next sign-in.
     */
    private function endSessionsFor(User $user): int
    {
        if (config('session.driver') !== 'database') {
            return 0;
        }

        return DB::table(config('session.table', 'sessions'))
            ->where('user_id', $user->id)
            ->delete();
    }

    /**
     * FR: Unlock Locked Accounts — Admin can manually unlock a user account
     * that has been locked due to repeated failed login attempts.
     */
    public function unlock(User $user)
    {
        // failed_login_attempts/locked_until are intentionally excluded from
        // $fillable (they must never be settable via a mass-assigned form),
        // so they're updated explicitly here via forceFill().
        $user->forceFill(['failed_login_attempts' => 0, 'locked_until' => null])->save();

        AuditLog::record('account_unlocked', "Unlocked account for {$user->email}");

        return back()->with('status', 'User account unlocked.');
    }
}
