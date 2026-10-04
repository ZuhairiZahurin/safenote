@php
    $statusTone = ['pending' => 'amber', 'in_review' => 'blue', 'closed' => 'green'];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('Referral') }}</h2>
    </x-slot>

    <x-page-head :title="$referral->student->name"
                 :subtitle="trim(($referral->student->class ?? __('No class recorded')).' · '.__('submitted').' '.$referral->created_at->format('j F Y'))"
                 icon="bi-send"
                 :tone="$statusTone[$referral->status] ?? 'slate'">
        <x-slot name="stats">
            <span class="sn-pill sn-pill-{{ $statusTone[$referral->status] ?? 'slate' }}">{{ $referral->status_label }}</span>
            <x-urgency-badge :urgency="$referral->urgency" :label="$referral->urgency_label" />
        </x-slot>
    </x-page-head>

    <div class="sn-record-grid">
        <div class="card shadow-sm">
            <div class="card-body">
                <h3 class="h6 mb-3">{{ __('Your Observation') }}</h3>
                <div class="sn-note-body">{{ $referral->notes }}</div>
            </div>
        </div>

        <div class="d-flex flex-column gap-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="sn-meta-list">
                        <div class="sn-meta-item">
                            <span class="sn-meta-label">{{ __('Status') }}</span>
                            <span class="sn-meta-value">{{ $referral->status_label }}</span>
                        </div>
                        <div class="sn-meta-item">
                            <span class="sn-meta-label">{{ __('Student') }}</span>
                            <span class="sn-meta-value">{{ $referral->student->name }}</span>
                        </div>
                        <div class="sn-meta-item">
                            <span class="sn-meta-label">{{ __('Class') }}</span>
                            <span class="sn-meta-value">{{ $referral->student->class ?? '—' }}</span>
                        </div>
                        <div class="sn-meta-item">
                            <span class="sn-meta-label">{{ __('Reason') }}</span>
                            <span class="sn-meta-value">{{ $referral->issue_type_label }}</span>
                        </div>
                        <div class="sn-meta-item">
                            <span class="sn-meta-label">{{ __('Submitted') }}</span>
                            <span class="sn-meta-value">{{ $referral->created_at->format('d M Y, H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sn-confidential">
                <i class="bi bi-shield-lock"></i>
                <span>{{ __('The counselling notes arising from this referral are confidential under Akta Kaunselor 1998. You see the status above and nothing further.') }}</span>
            </div>
        </div>
    </div>

    <a href="{{ route('referrals.index') }}" class="btn btn-link mt-3 ps-0">
        <i class="bi bi-arrow-left me-1"></i>{{ __('Back to my referrals') }}
    </a>
</x-app-layout>
