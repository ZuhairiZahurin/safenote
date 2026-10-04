<x-guest-layout>
    <h2>{{ __('Choose your own password') }}</h2>
    <p class="sn-auth-sub">
        {{ __('The password you signed in with was issued by the administrator, so it is not yours alone yet.') }}
    </p>

    <div class="sn-notice mb-4">
        <i class="bi bi-shield-lock"></i>
        <div>{{ __('Until you set a new one, the rest of SafeNote stays closed. Nobody else should know the password that opens counselling records.') }}</div>
    </div>

    <form method="POST" action="{{ route('password.change.update') }}" id="changeForm">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <x-input-label for="current_password" :value="__('Password given to you')" />
            <div class="sn-field">
                <x-text-input id="current_password" type="password" name="current_password" required autofocus
                              autocomplete="current-password" placeholder="••••••••" class="has-reveal" />
                <i class="bi bi-key"></i>
                <button type="button" class="sn-reveal" data-reveals="current_password"
                        aria-label="{{ __('Show password') }}" aria-pressed="false">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('current_password')" />
        </div>

        <div class="mb-3">
            <x-input-label for="password" :value="__('New password')" />
            <div class="sn-field">
                <x-text-input id="password" type="password" name="password" required
                              autocomplete="new-password" placeholder="{{ __('At least 8 characters') }}" class="has-reveal" />
                <i class="bi bi-lock"></i>
                <button type="button" class="sn-reveal" data-reveals="password"
                        aria-label="{{ __('Show password') }}" aria-pressed="false">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="mb-4">
            <x-input-label for="password_confirmation" :value="__('Confirm new password')" />
            <div class="sn-field">
                <x-text-input id="password_confirmation" type="password" name="password_confirmation" required
                              autocomplete="new-password" placeholder="••••••••" />
                <i class="bi bi-lock-fill"></i>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <button type="submit" class="btn btn-primary w-100" id="changeButton">
            {{ __('Save and continue') }}
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-3 text-center">
        @csrf
        <button type="submit" class="btn btn-link btn-sm text-decoration-none">{{ __('Sign out instead') }}</button>
    </form>

    <script>
        (() => {
            const labels = { show: @json(__('Show password')), hide: @json(__('Hide password')) };

            document.querySelectorAll('.sn-reveal').forEach((toggle) => {
                const field = document.getElementById(toggle.dataset.reveals);

                toggle.addEventListener('mousedown', (event) => event.preventDefault());

                toggle.addEventListener('click', () => {
                    const revealed = field.type === 'text';
                    const typing = document.activeElement === field;
                    const caret = field.selectionStart;

                    field.type = revealed ? 'password' : 'text';
                    toggle.setAttribute('aria-pressed', String(!revealed));
                    toggle.setAttribute('aria-label', revealed ? labels.show : labels.hide);
                    toggle.querySelector('i').className = revealed ? 'bi bi-eye' : 'bi bi-eye-slash';

                    if (typing) {
                        field.focus();
                        field.setSelectionRange(caret, caret);
                    }
                });
            });
        })();
    </script>
</x-guest-layout>
