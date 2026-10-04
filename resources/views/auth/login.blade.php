<x-guest-layout>
    <h2>{{ __('Sign in to SafeNote') }}</h2>
    <p class="sn-auth-sub">{{ __('Use the account issued to you by the administrator.') }}</p>

    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" id="loginForm">
        @csrf

        <div class="mb-3">
            <x-input-label for="email" :value="__('Email')" />
            <div class="sn-field">
                <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus
                              autocomplete="username" placeholder="nama@safenote.test" />
                <i class="bi bi-envelope"></i>
            </div>
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="mb-3">
            <x-input-label for="password" :value="__('Password')" />
            <div class="sn-field">
                <x-text-input id="password" type="password" name="password" required
                              autocomplete="current-password" placeholder="••••••••"
                              class="has-reveal" />
                <i class="bi bi-key"></i>
                <button type="button" class="sn-reveal" id="togglePassword"
                        aria-label="{{ __('Show password') }}" aria-pressed="false">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="form-check mb-0">
                <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                <label class="form-check-label small" for="remember_me">{{ __('Remember me') }}</label>
            </div>

            @if (Route::has('password.request'))
                <a class="small text-decoration-none" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        <button type="submit" class="btn btn-primary w-100" id="loginButton">{{ __('Log in') }}</button>

        <p class="sn-auth-note text-center mt-4 mb-0">
            <i class="bi bi-shield-lock"></i>
            {{ __('Authorised school staff only. All access is recorded.') }}
        </p>
    </form>

    <script>
        (() => {
            const field = document.getElementById('password');
            const toggle = document.getElementById('togglePassword');

            const labels = { show: @json(__('Show password')), hide: @json(__('Hide password')) };

            // Pressing the button must not move the caret out of the field, so the
            // user can keep typing where they left off.
            toggle.addEventListener('mousedown', (event) => event.preventDefault());

            toggle.addEventListener('click', () => {
                const revealed = field.type === 'text';
                const typing = document.activeElement === field;
                const caret = field.selectionStart;

                field.type = revealed ? 'password' : 'text';
                toggle.setAttribute('aria-pressed', String(!revealed));
                toggle.setAttribute('aria-label', revealed ? labels.show : labels.hide);
                toggle.querySelector('i').className = revealed ? 'bi bi-eye' : 'bi bi-eye-slash';

                // Changing the type clears the selection, so put the caret back
                // instead of leaving the whole password selected and overwritable.
                if (typing) {
                    field.focus();
                    field.setSelectionRange(caret, caret);
                }
            });

            // Shows the request is on its way, and blocks a second submission.
            document.getElementById('loginForm').addEventListener('submit', (event) => {
                const button = document.getElementById('loginButton');
                if (button.classList.contains('is-busy')) return event.preventDefault();
                button.classList.add('is-busy');
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>{{ __('Signing in…') }}';
            });
        })();
    </script>
</x-guest-layout>
