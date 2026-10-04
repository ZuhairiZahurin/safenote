@php
    $max = max($teacherStats['maxIssueCount'], 1);
    $statusTone = ['pending' => 'amber', 'in_review' => 'blue', 'closed' => 'green'];
@endphp

<div class="viz viz-animate">
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <x-stat-tile :label="__('Referrals Made')" :value="$teacherStats['total']"
                         :note="__('All time')" icon="bi-send" tone="blue"
                         :href="route('referrals.index')" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-tile :label="__('Pending')" :value="$teacherStats['pending']"
                         :note="__('Awaiting counsellor')" icon="bi-hourglass-split"
                         :tone="$teacherStats['pending'] > 0 ? 'amber' : 'slate'" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-tile :label="__('In Review')" :value="$teacherStats['inReview']"
                         :note="__('Being handled')" icon="bi-eye" tone="slate" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-tile :label="__('Students Referred')" :value="$teacherStats['students']"
                         :note="__('Distinct students')" icon="bi-people" tone="green" />
        </div>
    </div>

    @if ($teacherStats['total'] === 0)
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-send fs-1 text-muted d-block mb-2"></i>
                <p class="text-muted mb-3">{{ __('You have not referred any students yet. Refer a student when you notice something the counselling unit should know about.') }}</p>
                <a href="{{ route('referrals.create') }}" class="btn btn-primary btn-sm">{{ __('Refer a student') }}</a>
            </div>
        </div>
    @else
        <div class="row g-3 mb-3">
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h3 class="h6 mb-1">{{ __('Referral Progress') }}</h3>
                        <p class="small text-muted mb-3">{{ __('Status of the referrals you submitted.') }}</p>

                        @foreach ($teacherStats['byStatus'] as $i => $row)
                            @php $pct = $teacherStats['total'] > 0 ? round($row['count'] / $teacherStats['total'] * 100) : 0; @endphp
                            <div class="viz-bar-row">
                                <div class="d-flex justify-content-between align-items-baseline mb-1">
                                    <span class="viz-bar-label">{{ $row['label'] }}</span>
                                    <span class="viz-bar-value">{{ $row['count'] }} <span class="fw-normal text-muted">({{ $pct }}%)</span></span>
                                </div>
                                <div class="viz-bar-track">
                                    <div class="viz-bar-fill viz-tone-{{ $i + 1 }} {{ $row['count'] === 0 ? 'is-zero' : '' }}"
                                         style="--w: {{ $row['count'] > 0 ? max($pct, 2) : 0 }}%; --i: {{ $i }};"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h3 class="h6 mb-1">{{ __('Reasons You Referred') }}</h3>
                        <p class="small text-muted mb-3">{{ __('Most frequent concerns you raised.') }}</p>

                        @foreach ($teacherStats['byIssue'] as $i => $row)
                            <div class="viz-bar-row">
                                <div class="d-flex justify-content-between align-items-baseline mb-1">
                                    <span class="viz-bar-label">{{ $row['label'] }}</span>
                                    <span class="viz-bar-value">{{ $row['count'] }}</span>
                                </div>
                                <div class="viz-bar-track">
                                    <div class="viz-bar-fill" style="--w: {{ max(round($row['count'] / $max * 100), 2) }}%; --i: {{ $i }};"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="sn-card-head">
                    <div>
                        <h3 class="h6 mb-1">{{ __('Your Recent Referrals') }}</h3>
                        <p class="small text-muted mb-0">{{ __('Status only. Counselling notes are never shown to teachers.') }}</p>
                    </div>
                    <a href="{{ route('referrals.index') }}" class="small text-decoration-none">{{ __('All referrals') }}</a>
                </div>

                @foreach ($teacherStats['recent'] as $referral)
                    <a href="{{ route('referrals.show', $referral) }}" class="sn-feed-row">
                        <span class="sn-urgency sn-urgency-{{ $referral->urgency }}" title="{{ $referral->urgency_label }}"></span>
                        <span class="sn-feed-main">
                            <span class="sn-feed-title">{{ $referral->student?->name ?? __('Unknown student') }}</span>
                            <span class="sn-feed-sub">{{ $referral->issue_type_label }}</span>
                        </span>
                        <span class="sn-pill sn-pill-{{ $statusTone[$referral->status] ?? 'slate' }}">{{ $referral->status_label }}</span>
                        <span class="sn-feed-meta">{{ $referral->created_at->format('j M') }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
