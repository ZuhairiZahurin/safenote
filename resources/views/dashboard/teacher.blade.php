@php
    $max = max($teacherStats['maxIssueCount'], 1);
@endphp

<div class="viz">
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="viz-tile-label">{{ __('Referrals Made') }}</div>
                    <div class="viz-tile-value">{{ $teacherStats['total'] }}</div>
                    <div class="viz-tile-note">{{ __('All time') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="viz-tile-label">{{ __('Pending') }}</div>
                    <div class="viz-tile-value">{{ $teacherStats['pending'] }}</div>
                    <div class="viz-tile-note">{{ __('Awaiting counsellor') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="viz-tile-label">{{ __('In Review') }}</div>
                    <div class="viz-tile-value">{{ $teacherStats['inReview'] }}</div>
                    <div class="viz-tile-note">{{ __('Being handled') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="viz-tile-label">{{ __('Students Referred') }}</div>
                    <div class="viz-tile-value">{{ $teacherStats['students'] }}</div>
                    <div class="viz-tile-note">{{ __('Distinct students') }}</div>
                </div>
            </div>
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
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h3 class="h6 mb-1">{{ __('Referral Progress') }}</h3>
                        <p class="small text-muted mb-3">{{ __('Status of the referrals you submitted.') }}</p>

                        @foreach ($teacherStats['byStatus'] as $row)
                            @php $pct = $teacherStats['total'] > 0 ? round($row['count'] / $teacherStats['total'] * 100) : 0; @endphp
                            <div class="viz-bar-row">
                                <div class="d-flex justify-content-between align-items-baseline mb-1">
                                    <span class="viz-bar-label">{{ $row['label'] }}</span>
                                    <span class="viz-bar-value">{{ $row['count'] }} <span class="fw-normal text-muted">({{ $pct }}%)</span></span>
                                </div>
                                <div class="viz-bar-track">
                                    <div class="viz-bar-fill {{ $row['count'] === 0 ? 'is-zero' : '' }}"
                                         style="width: {{ $row['count'] > 0 ? max($pct, 2) : 0 }}%;"></div>
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

                        @foreach ($teacherStats['byIssue'] as $row)
                            <div class="viz-bar-row">
                                <div class="d-flex justify-content-between align-items-baseline mb-1">
                                    <span class="viz-bar-label">{{ $row['label'] }}</span>
                                    <span class="viz-bar-value">{{ $row['count'] }}</span>
                                </div>
                                <div class="viz-bar-track">
                                    <div class="viz-bar-fill" style="width: {{ max(round($row['count'] / $max * 100), 2) }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
