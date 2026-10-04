<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('Referral — ') }}{{ $referral->student->name }}</h2>
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">{{ __('Status') }}</dt>
                <dd class="col-sm-9">
                    <span class="badge text-bg-{{ $referral->status_variant }}">{{ $referral->status_label }}</span>
                </dd>

                <dt class="col-sm-3">{{ __('Class') }}</dt>
                <dd class="col-sm-9">{{ $referral->student->class ?? '—' }}</dd>

                <dt class="col-sm-3">{{ __('Reason') }}</dt>
                <dd class="col-sm-9">{{ $referral->issue_type_label }}</dd>

                <dt class="col-sm-3">{{ __('Urgency') }}</dt>
                <dd class="col-sm-9"><x-urgency-badge :urgency="$referral->urgency" :label="$referral->urgency_label" /></dd>

                <dt class="col-sm-3">{{ __('Submitted') }}</dt>
                <dd class="col-sm-9">{{ $referral->created_at->format('d M Y, H:i') }}</dd>

                <dt class="col-sm-3">{{ __('Your Observation') }}</dt>
                <dd class="col-sm-9" style="white-space: pre-wrap;">{{ $referral->notes }}</dd>
            </dl>
        </div>
    </div>

    <div class="alert alert-light border small mt-3">
        <i class="bi bi-shield-lock"></i>
        {{ __('The counselling session notes arising from this referral are confidential under Akta Kaunselor 1998 and are not accessible to teachers. You can see only the status above.') }}
    </div>

    <a href="{{ route('referrals.index') }}" class="btn btn-link ps-0">&larr; {{ __('Back to my referrals') }}</a>
</x-app-layout>
