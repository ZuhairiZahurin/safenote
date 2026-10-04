<?php

namespace App\Http\Requests\Auth;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Number of consecutive failed attempts before an account is locked.
     */
    private const MAX_ATTEMPTS = 5;

    /**
     * Temporary rather than indefinite, so an attacker who knows a user's email
     * cannot keep that account locked out (a denial-of-service on Availability).
     */
    public const LOCKOUT_MINUTES = 15;

    /**
     * Failed attempts allowed per IP address per minute, across all accounts.
     */
    private const MAX_FAILURES_PER_IP = 20;

    /**
     * One message for every failure so responses never reveal whether an
     * email address exists, or is locked or deactivated.
     */
    public const FAILED_MESSAGE = 'These credentials could not be verified, or the account is temporarily unavailable. Contact an administrator if this continues.';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials, enforcing SafeNote's
     * brute-force account lockout (Availability objective, 1.4.3).
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $ipKey = 'login-failures:'.$this->ip();

        if (RateLimiter::tooManyAttempts($ipKey, self::MAX_FAILURES_PER_IP)) {
            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', ['seconds' => RateLimiter::availableIn($ipKey)]),
            ]);
        }

        $user = User::where('email', $this->string('email'))->first();

        if (($user && ($user->isLocked() || ! $user->is_active))
            || ! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($ipKey, 60);

            if ($user && ! $user->isLocked() && $user->is_active) {
                $this->recordFailedAttempt($user);
            } else {
                AuditLog::record('failed_login', "Failed login attempt for {$this->string('email')}", $user?->id);
            }

            throw ValidationException::withMessages(['email' => self::FAILED_MESSAGE]);
        }

        $user = Auth::user();
        $user->forceFill([
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ])->save();

        AuditLog::record('login', "Successful login for {$user->email}", $user->id);
    }

    private function recordFailedAttempt(User $user): void
    {
        AuditLog::record('failed_login', "Failed login attempt for {$this->string('email')}", $user->id);

        $attempts = $user->failed_login_attempts + 1;
        // The counter is only reset by a successful login or an admin unlock, so
        // once past the threshold every further failure re-locks immediately.
        $locked = $attempts >= self::MAX_ATTEMPTS;

        $user->forceFill([
            'failed_login_attempts' => $attempts,
            'locked_until' => $locked ? now()->addMinutes(self::LOCKOUT_MINUTES) : null,
        ])->save();

        if ($locked) {
            AuditLog::record(
                'account_locked',
                "Account locked for ".self::LOCKOUT_MINUTES." minutes after {$attempts} failed attempts",
                $user->id
            );
        }
    }
}
