<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h4 mb-0">{{ __('Referral Inbox') }}</h2>
            @if ($pendingCount > 0)
                <span class="sn-wait-note">
                    <i class="bi bi-clock-history"></i>
                    {{ trans_choice('{1} :count referral awaiting review|[2,*] :count referrals awaiting review', $pendingCount, ['count' => $pendingCount]) }}
                    @if ($longestWait)
                        &middot; {{ __('oldest waiting :days days', ['days' => (int) abs(\Illuminate\Support\Carbon::parse($longestWait)->diffInDays())]) }}
                    @endif
                </span>
            @endif
        </div>
    </x-slot>

    {{-- Status filter as tabs, so the workload is visible without opening a menu --}}
    <div class="sn-tabs mb-3">
        @php
            $tabs = ['' => __('All')] + \App\Models\Referral::STATUSES;
        @endphp
        @foreach ($tabs as $key => $label)
            @php
                $count = $key === '' ? $totalCount : (int) ($counts[$key] ?? 0);
            @endphp
            <a href="{{ route('referral-inbox.index', $key === '' ? [] : ['status' => $key]) }}"
               class="sn-tab {{ $status === $key ? 'is-active' : '' }}">
                {{ $label }}
                <span class="sn-tab-count">{{ $count }}</span>
            </a>
        @endforeach
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 sn-inbox">
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
                                <div class="fw-semibold">{{ $referral->student->name }}</div>
                                <div class="sn-sub">{{ $referral->student->class ?? '—' }}</div>
                            </td>
                            <td><span class="badge text-bg-light border">{{ $referral->issue_type_label }}</span></td>
                            <td class="sn-sub">{{ $referral->teacher->name ?? '—' }}</td>
                            <td>
                                <span class="{{ $isOverdue ? 'sn-overdue' : 'sn-sub' }}">
                                    @if ($days === 0)
                                        {{ __('Today') }}
                                    @else
                                        {{ trans_choice('{1} :count day|[2,*] :count days', $days, ['count' => $days]) }}
                                    @endif
                                </span>
                                <div class="sn-sub">{{ $referral->created_at->format('d M Y') }}</div>
                            </td>
                            <td><span class="badge text-bg-{{ $referral->status_variant }}">{{ $referral->status_label }}</span></td>
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
                                <div class="sn-empty">
                                    <i class="bi bi-inbox"></i>
                                    <p class="mb-0">
                                        {{ $status ? __('No referrals with this status.') : __('No referrals have been submitted yet.') }}
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
