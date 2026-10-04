<x-guest-layout>
    @php($support = config('school.support'))

    <h2>{{ __('Forgotten your password?') }}</h2>
    <p class="sn-auth-sub">{{ __('A new password is issued by the school administrator, in person.') }}</p>

    <div class="sn-notice mb-4">
        <i class="bi bi-shield-lock"></i>
        <div>
            {{ __('SafeNote does not email reset links. These accounts open confidential student records, so the holder is identified face to face before a new password is given out.') }}
        </div>
    </div>

    <ol class="sn-steps">
        <li>
            <span class="sn-step-title">{{ __('Go to the administrator') }}</span>
            <span class="sn-step-body">{{ $support['office'] }} — {{ $support['hours'] }}</span>
        </li>
        <li>
            <span class="sn-step-title">{{ __('Bring your staff identification') }}</span>
            <span class="sn-step-body">{{ __('Your identity is confirmed before anything is changed.') }}</span>
        </li>
        <li>
            <span class="sn-step-title">{{ __('Receive a temporary password') }}</span>
            <span class="sn-step-body">{{ __('Any session still signed in as you is ended at the same moment.') }}</span>
        </li>
        <li>
            <span class="sn-step-title">{{ __('Change it once you are back in') }}</span>
            <span class="sn-step-body">{{ __('Profile → Update Password, so only you know it.') }}</span>
        </li>
    </ol>

    <div class="sn-contact">
        <div class="sn-contact-row">
            <i class="bi bi-person-badge"></i>
            <span>{{ $support['contact'] }}</span>
        </div>
        <div class="sn-contact-row">
            <i class="bi bi-telephone"></i>
            <a href="tel:{{ preg_replace('/\s+/', '', $support['phone']) }}">{{ $support['phone'] }}</a>
        </div>
        <div class="sn-contact-row">
            <i class="bi bi-envelope"></i>
            <a href="mailto:{{ $support['email'] }}">{{ $support['email'] }}</a>
        </div>
    </div>

    <a href="{{ route('login') }}" class="btn btn-primary w-100 mt-4">
        <i class="bi bi-arrow-left me-2"></i>{{ __('Back to sign in') }}
    </a>
</x-guest-layout>
