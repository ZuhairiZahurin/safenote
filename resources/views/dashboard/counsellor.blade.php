@php
    $total = $stats['total'];
    $max = max($stats['maxIssueCount'], 1);
    $maxMonth = max(collect($stats['monthly'])->max('count'), 1);

    // One colour per case category, used by every chart on this page so they
    // all agree. Keyed off the model's own constants rather than retyped.
    $categoryTone = [
        \App\Models\CounsellingRecord::CATEGORY_ACADEMIC => 1,
        \App\Models\CounsellingRecord::CATEGORY_BEHAVIOURAL => 2,
        \App\Models\CounsellingRecord::CATEGORY_EMOTIONAL_WELFARE => 3,
    ];
@endphp

<div class="viz viz-animate">

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <x-stat-tile :label="__('Total Cases')" :value="$total" :note="__('All time')"
                         icon="bi-folder2-open" tone="blue" :href="route('records.index')" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-tile :label="__('This Month')" :value="$stats['thisMonth']"
                         :note="now()->format('F Y')" icon="bi-calendar-event" tone="green"
                         :change="$stats['lastMonth'] > 0 || $stats['thisMonth'] > 0 ? $stats['monthChange'] : null" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-tile :label="__('Students Supported')" :value="$stats['students']"
                         :note="__('Distinct students')" icon="bi-people" tone="slate" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-tile :label="__('Waiting on You')" :value="$stats['waitingTotal']"
                         :note="__('Pending referrals')" icon="bi-inbox"
                         :tone="$stats['waitingTotal'] > 0 ? 'amber' : 'slate'"
                         :href="route('referral-inbox.index')" />
        </div>
    </div>

    @if ($total === 0)
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-bar-chart fs-1 text-muted d-block mb-2"></i>
                <p class="text-muted mb-3">{{ __('No counselling records yet. Statistics will appear here once you record your first session.') }}</p>
                <a href="{{ route('records.create') }}" class="btn btn-primary btn-sm">{{ __('Create the first record') }}</a>
            </div>
        </div>
    @else
        <div class="row g-3 mb-3">

            {{-- Cases by presenting issue ---------------------------------- --}}
            <div class="col-lg-7">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="sn-card-head">
                            <div>
                                <h3 class="h6 mb-1">{{ __('Cases by Presenting Issue') }}</h3>
                                <p class="small text-muted mb-0">{{ __('Highest first. Select an issue to open those records.') }}</p>
                            </div>
                            <span class="sn-card-total">{{ $total }}</span>
                        </div>

                        @foreach ($stats['byIssue'] as $i => $issue)
                            @php
                                $pct = $total > 0 ? round($issue['count'] / $total * 100) : 0;
                                $tone = $categoryTone[$issue['category']] ?? 4;
                            @endphp
                            <div class="viz-bar-row">
                                <div class="d-flex justify-content-between align-items-baseline mb-1">
                                    <span class="viz-bar-label">
                                        @if ($issue['key'])
                                            <a href="{{ route('records.index', ['issue_type' => $issue['key']]) }}">{{ $issue['label'] }}</a>
                                        @else
                                            {{ $issue['label'] }}
                                        @endif
                                    </span>
                                    <span class="viz-bar-value">{{ $issue['count'] }} <span class="fw-normal text-muted">({{ $pct }}%)</span></span>
                                </div>
                                <div class="viz-bar-track"
                                     data-bs-toggle="tooltip"
                                     title="{{ $issue['label'] }}: {{ $issue['count'] }} of {{ $total }} cases ({{ $pct }}%)">
                                    <div class="viz-bar-fill viz-tone-{{ $tone }} {{ $issue['count'] === 0 ? 'is-zero' : '' }}"
                                         style="--w: {{ $issue['count'] > 0 ? max(round($issue['count'] / $max * 100), 2) : 0 }}%; --i: {{ $i }};"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-5">

                {{-- Category split ----------------------------------------- --}}
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <h3 class="h6 mb-1">{{ __('Cases by Category') }}</h3>
                        <p class="small text-muted mb-3">{{ __('Share of your caseload across the three categories.') }}</p>

                        <div class="viz-stack mb-3">
                            @foreach ($stats['byCategory'] as $i => $cat)
                                @if ($cat['count'] > 0)
                                    <div class="viz-stack-seg viz-tone-{{ $categoryTone[$cat['key']] ?? 4 }}"
                                         style="--w: {{ $cat['count'] / $total * 100 }}%; --i: {{ $i }};"
                                         data-bs-toggle="tooltip"
                                         title="{{ $cat['label'] }}: {{ $cat['count'] }} ({{ round($cat['count'] / $total * 100) }}%)"></div>
                                @endif
                            @endforeach
                        </div>

                        {{-- The label and count are text, so identity is never
                             carried by colour alone. --}}
                        @foreach ($stats['byCategory'] as $i => $cat)
                            <div class="sn-legend-row" style="--i: {{ $i }};">
                                <span class="viz-swatch viz-tone-{{ $categoryTone[$cat['key']] ?? 4 }}"></span>
                                <span class="viz-legend-label flex-grow-1">{{ $cat['label'] }}</span>
                                <span class="viz-legend-value">
                                    {{ $cat['count'] }}
                                    <span class="fw-normal text-muted">({{ $total > 0 ? round($cat['count'] / $total * 100) : 0 }}%)</span>
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Monthly trend ------------------------------------------ --}}
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="h6 mb-1">{{ __('Sessions Over Time') }}</h3>
                        <p class="small text-muted mb-3">{{ __('Last six months.') }}</p>

                        <div class="viz-columns">
                            <span class="viz-gridline" style="--at: 0%"></span>
                            <span class="viz-gridline" style="--at: 50%"></span>
                            <span class="viz-gridline" style="--at: 100%"></span>

                            @foreach ($stats['monthly'] as $i => $month)
                                <div class="viz-column-slot" style="--i: {{ $i }};"
                                     data-bs-toggle="tooltip"
                                     title="{{ $month['full'] }}: {{ trans_choice('{1} :count session|[2,*] :count sessions', $month['count'], ['count' => $month['count']]) }}">
                                    <span class="viz-column-value">{{ $month['count'] }}</span>
                                    <div class="viz-column-fill {{ $month['count'] === 0 ? 'is-zero' : '' }}"
                                         style="--h: {{ $month['count'] > 0 ? max(round($month['count'] / $maxMonth * 100), 3) : 2 }}%;"></div>
                                </div>
                            @endforeach
                        </div>
                        <div class="d-flex gap-2">
                            @foreach ($stats['monthly'] as $month)
                                <div class="viz-column-label flex-fill">{{ $month['label'] }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- What to do next, and what was done last --------------------------- --}}
        <div class="row g-3">
            <div class="col-lg-5">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="sn-card-head">
                            <h3 class="h6 mb-0">{{ __('Referrals Waiting') }}</h3>
                            <a href="{{ route('referral-inbox.index') }}" class="small text-decoration-none">{{ __('Open inbox') }}</a>
                        </div>

                        @forelse ($stats['waiting'] as $referral)
                            <a href="{{ route('referral-inbox.show', $referral) }}" class="sn-feed-row">
                                <span class="sn-urgency sn-urgency-{{ $referral->urgency }}"
                                      title="{{ $referral->urgency_label }}"></span>
                                <span class="sn-feed-main">
                                    <span class="sn-feed-title">{{ $referral->student?->name ?? __('Unknown student') }}</span>
                                    <span class="sn-feed-sub">{{ $referral->issue_type_label }}</span>
                                </span>
                                <span class="sn-feed-meta">{{ $referral->created_at->diffForHumans(short: true) }}</span>
                            </a>
                        @empty
                            <p class="sn-empty-note">
                                <i class="bi bi-check2-circle me-1"></i>{{ __('Nothing waiting. Every referral has been picked up.') }}
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="sn-card-head">
                            <h3 class="h6 mb-0">{{ __('Recent Sessions') }}</h3>
                            <a href="{{ route('records.index') }}" class="small text-decoration-none">{{ __('All records') }}</a>
                        </div>

                        @foreach ($stats['recent'] as $record)
                            <a href="{{ route('records.show', $record) }}" class="sn-feed-row">
                                <span class="sn-feed-dot viz-tone-{{ $categoryTone[$record->category] ?? 4 }}"></span>
                                <span class="sn-feed-main">
                                    <span class="sn-feed-title">{{ $record->student?->name ?? __('Unknown student') }}</span>
                                    <span class="sn-feed-sub">{{ $record->issue_type_label }}</span>
                                </span>
                                <span class="sn-feed-meta">{{ $record->session_date->format('j M Y') }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
