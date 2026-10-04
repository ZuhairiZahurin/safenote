@php
    $categoryTone = [
        \App\Models\CounsellingRecord::CATEGORY_ACADEMIC => 1,
        \App\Models\CounsellingRecord::CATEGORY_BEHAVIOURAL => 2,
        \App\Models\CounsellingRecord::CATEGORY_EMOTIONAL_WELFARE => 3,
    ];
    $statusTone = ['pending' => 'amber', 'in_review' => 'blue', 'closed' => 'green'];
    $oldestDays = $longestWait
        ? (int) abs(\Illuminate\Support\Carbon::parse($longestWait)->diffInDays())
        : null;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('Referral Inbox') }}</h2>
    </x-slot>

    <x-page-head :title="__('Referral Inbox')"
                 :subtitle="__('Concerns raised by teachers. Opening one does not tell the teacher what you write.')"
                 icon="bi-inbox"
                 :tone="$pendingCount > 0 ? 'amber' : 'green'">
        <x-slot name="stats">
            <x-meter-chip :label="__('awaiting review')" :value="$pendingCount"
                          :tone="$pendingCount > 0 ? 'amber' : 'green'" />
            @if ($oldestDays !== null && $pendingCount > 0)
                <x-meter-chip :label="__('days, oldest wait')" :value="$oldestDays"
                              :tone="$oldestDays >= 3 ? 'red' : 'slate'" />
            @endif
            <x-meter-chip :label="__('referrals in total')" :value="$totalCount" />
        </x-slot>

        <x-slot name="actions">
            <a href="{{ route('records.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>{{ __('New Record') }}
            </a>
        </x-slot>
    </x-page-head>

    {{-- Status filter as tabs, so the workload is visible without opening a menu --}}
    <div class="sn-tabs mb-3">
        @php $tabs = ['' => __('All')] + \App\Models\Referral::STATUSES; @endphp
        @foreach ($tabs as $key => $label)
            @php $count = $key === '' ? $totalCount : (int) ($counts[$key] ?? 0); @endphp
            <a href="{{ route('referral-inbox.index', $key === '' ? [] : ['status' => $key]) }}"
               class="sn-tab {{ $status === $key ? 'is-active' : '' }}">
                {{ $label }}
                <span class="sn-tab-count">{{ $count }}</span>
            </a>
        @endforeach
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table sn-table sn-inbox mb-0">
                <thead>
                    <tr>
                        <th>{{ __('Urgency') }}</th>
                        <th>{{ __('Student') }}</th>
                        <th>{{ __('Issue') }}</th>
                        <th>{{ __('Referred By') }}</th>
                        <th>{{ __('Waiting') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($referrals as $referral)
                        @php
                            // Carbon 3 returns a signed float here, so round it to whole days.
                            $days = (int) abs($referral->created_at->diffInDays());
                            $isOverdue = $referral->status === \App\Models\Referral::STATUS_PENDING && $days >= 3;
                        @endphp
                        <tr class="{{ $referral->urgency === 'high' && $referral->status !== \App\Models\Referral::STATUS_CLOSED ? 'sn-row-urgent' : '' }}">
                            <td><x-urgency-badge :urgency="$referral->urgency" :label="$referral->urgency_label" /></td>
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
                            <td class="sn-cell-sub">{{ $referral->teacher->name ?? '—' }}</td>
                            <td>
                                <div class="{{ $isOverdue ? 'sn-overdue' : 'sn-cell-primary' }}">
                                    @if ($days === 0)
                                        {{ __('Today') }}
                                    @else
                                        {{ trans_choice('{1} :count day|[2,*] :count days', $days, ['count' => $days]) }}
                                    @endif
                                </div>
                                <div class="sn-cell-sub">{{ $referral->created_at->format('d M Y') }}</div>
                            </td>
                            <td>
                                <span class="sn-pill sn-pill-{{ $statusTone[$referral->status] ?? 'slate' }}">{{ $referral->status_label }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('referral-inbox.show', $referral) }}"
                                   class="btn btn-sm {{ $referral->status === \App\Models\Referral::STATUS_PENDING ? 'btn-primary' : 'btn-outline-primary' }}">
                                    {{ $referral->status === \App\Models\Referral::STATUS_PENDING ? __('Review') : __('Open') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="sn-blank">
                                    <i class="bi bi-inbox"></i>
                                    <div class="sn-blank-title">
                                        {{ $status ? __('Nothing with this status.') : __('The inbox is empty.') }}
                                    </div>
                                    <p class="mb-0">
                                        {{ $status
                                            ? __('Choose another tab to see the rest.')
                                            : __('Referrals raised by teachers will arrive here.') }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $referrals->links() }}
    </div>
</x-app-layout>
