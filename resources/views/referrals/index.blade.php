@php
    $categoryTone = [
        \App\Models\CounsellingRecord::CATEGORY_ACADEMIC => 1,
        \App\Models\CounsellingRecord::CATEGORY_BEHAVIOURAL => 2,
        \App\Models\CounsellingRecord::CATEGORY_EMOTIONAL_WELFARE => 3,
    ];
    $statusTone = ['pending' => 'amber', 'in_review' => 'blue', 'closed' => 'green'];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('My Referrals') }}</h2>
    </x-slot>

    <x-page-head :title="__('My Referrals')"
                 :subtitle="__('The progress of concerns you raised. Status only.')"
                 icon="bi-send" tone="blue">
        <x-slot name="stats">
            <x-meter-chip :label="$status ? __('with this status') : __('referrals')" :value="$referrals->total()" tone="blue" />
        </x-slot>

        <x-slot name="actions">
            <a href="{{ route('referrals.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>{{ __('Refer a Student') }}
            </a>
        </x-slot>
    </x-page-head>

    <div class="sn-tabs mb-3">
        @php $tabs = ['' => __('All')] + \App\Models\Referral::STATUSES; @endphp
        @foreach ($tabs as $key => $label)
            <a href="{{ route('referrals.index', $key === '' ? [] : ['status' => $key]) }}"
               class="sn-tab {{ $status === $key ? 'is-active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table sn-table mb-0">
                <thead>
                    <tr>
                        <th>{{ __('Student') }}</th>
                        <th>{{ __('Issue') }}</th>
                        <th>{{ __('Urgency') }}</th>
                        <th>{{ __('Submitted') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($referrals as $referral)
                        <tr>
                            <td>
                                <div class="sn-cell-primary">{{ $referral->student->name }}</div>
                                <div class="sn-cell-sub">{{ $referral->student->class ?? '—' }}</div>
                            </td>
                            <td>
                                <span class="sn-tag">
                                    <span class="sn-tag-dot viz-tone-{{ $categoryTone[\App\Models\CounsellingRecord::ISSUE_TYPES[$referral->issue_type]['category'] ?? ''] ?? 4 }}"></span>
                                    {{ $referral->issue_type_label }}
                                </span>
                            </td>
                            <td><x-urgency-badge :urgency="$referral->urgency" :label="$referral->urgency_label" /></td>
                            <td>
                                <div class="sn-cell-primary">{{ $referral->created_at->format('d M Y') }}</div>
                                <div class="sn-cell-sub">{{ $referral->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                <span class="sn-pill sn-pill-{{ $statusTone[$referral->status] ?? 'slate' }}">{{ $referral->status_label }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('referrals.show', $referral) }}" class="btn btn-sm btn-outline-primary">{{ __('Open') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="sn-blank">
                                    <i class="bi bi-send"></i>
                                    <div class="sn-blank-title">
                                        {{ $status ? __('Nothing with this status.') : __('No referrals yet.') }}
                                    </div>
                                    <p class="mb-0">
                                        {{ $status
                                            ? __('Choose another tab to see the rest.')
                                            : __('Refer a student when you notice something the counselling unit should know about.') }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="sn-confidential mt-3">
        <i class="bi bi-shield-lock"></i>
        <span>{{ __('You see the status of your referrals, never the counselling notes that follow from them. That boundary is deliberate.') }}</span>
    </div>

    <div class="mt-3">
        {{ $referrals->links() }}
    </div>
</x-app-layout>
