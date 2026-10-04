@php
    $total = $stats['total'];
    $max = max($stats['maxIssueCount'], 1);
    $maxMonth = max(collect($stats['monthly'])->max('count'), 1);
    $seriesColours = ['var(--series-1)', 'var(--series-2)', 'var(--series-3)'];
@endphp

<div class="viz viz-animate">

    {{-- KPI row ---------------------------------------------------------- --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="viz-tile-label">{{ __('Total Cases') }}</div>
                    <div class="viz-tile-value viz-count" data-value="{{ $total }}">{{ $total }}</div>
                    <div class="viz-tile-note">{{ __('All time') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="viz-tile-label">{{ __('This Month') }}</div>
                    <div class="viz-tile-value viz-count" data-value="{{ $stats['thisMonth'] }}">{{ $stats['thisMonth'] }}</div>
                    <div class="viz-tile-note">{{ now()->format('F Y') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="viz-tile-label">{{ __('Students Supported') }}</div>
                    <div class="viz-tile-value viz-count" data-value="{{ $stats['students'] }}">{{ $stats['students'] }}</div>
                    <div class="viz-tile-note">{{ __('Distinct students') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="viz-tile-label">{{ __('Most Common Issue') }}</div>
                    @if ($stats['topIssue'])
                        <div class="fs-5 fw-semibold" style="color: var(--text-primary);">{{ $stats['topIssue']['label'] }}</div>
                        <div class="viz-tile-note">
                            {{ trans_choice('{1} :count case|[2,*] :count cases', $stats['topIssue']['count'], ['count' => $stats['topIssue']['count']]) }}
                        </div>
                    @else
                        <div class="fs-5 fw-semibold text-muted">—</div>
                        <div class="viz-tile-note">{{ __('No cases recorded yet') }}</div>
                    @endif
                </div>
            </div>
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
        <div class="row g-3">

            {{-- Cases by presenting issue ---------------------------------- --}}
            <div class="col-lg-7">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h3 class="h6 mb-1">{{ __('Cases by Presenting Issue') }}</h3>
                        <p class="small text-muted mb-3">{{ __('Your caseload, highest first. Click an issue to see those records.') }}</p>

                        @foreach ($stats['byIssue'] as $i => $issue)
                            @php $pct = $total > 0 ? round($issue['count'] / $total * 100) : 0; @endphp
                            <div class="viz-bar-row">
                                <div class="d-flex justify-content-between align-items-baseline mb-1">
                                    <span class="viz-bar-label">
                                        @if ($issue['key'])
                                            <a href="{{ route('records.index', ['issue_type' => $issue['key']]) }}"
                                               class="text-decoration-none" style="color: inherit;">{{ $issue['label'] }}</a>
                                        @else
                                            {{ $issue['label'] }}
                                        @endif
                                    </span>
                                    <span class="viz-bar-value">{{ $issue['count'] }} <span class="fw-normal text-muted">({{ $pct }}%)</span></span>
                                </div>
                                <div class="viz-bar-track"
                                     data-bs-toggle="tooltip"
                                     title="{{ $issue['label'] }}: {{ $issue['count'] }} of {{ $total }} cases ({{ $pct }}%)">
                                    <div class="viz-bar-fill {{ $issue['count'] === 0 ? 'is-zero' : '' }}"
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
                        <p class="small text-muted mb-3">{{ __('Share of your caseload across the three case categories.') }}</p>

                        <div class="viz-stack mb-3">
                            @foreach ($stats['byCategory'] as $i => $cat)
                                @if ($cat['count'] > 0)
                                    <div class="viz-stack-seg"
                                         style="--w: {{ $cat['count'] / $total * 100 }}%; --i: {{ $i }}; background: {{ $seriesColours[$i] }};"
                                         data-bs-toggle="tooltip"
                                         title="{{ $cat['label'] }}: {{ $cat['count'] }} ({{ round($cat['count'] / $total * 100) }}%)"></div>
                                @endif
                            @endforeach
                        </div>

                        {{-- Legend carries the label and count as text, so identity
                             is never conveyed by colour alone. --}}
                        @foreach ($stats['byCategory'] as $i => $cat)
                            <div class="d-flex align-items-center gap-2 mb-1" style="--i: {{ $i }};">
                                <span class="viz-swatch" style="background: {{ $seriesColours[$i] }};"></span>
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
    @endif
</div>

<script>
    // Counts the KPI tiles up from zero. The final value is already in the HTML,
    // so the figures are correct with JavaScript disabled or motion reduced.
    (() => {
        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduced) return;

        document.querySelectorAll('.viz-count').forEach((el, index) => {
            const target = Number(el.dataset.value || 0);
            if (target === 0) return;

            const duration = 900;
            const delay = index * 90;
            let startedAt = null;
            let settled = false;
            el.textContent = '0';

            const settle = () => {
                settled = true;
                el.textContent = target;
            };

            const step = (now) => {
                if (settled) return;
                startedAt ??= now;
                const progress = Math.min(Math.max(now - startedAt - delay, 0) / duration, 1);
                if (progress >= 1) return settle();
                el.textContent = Math.round(target * (1 - Math.pow(1 - progress, 3)));
                requestAnimationFrame(step);
            };

            requestAnimationFrame(step);

            // Browsers throttle animation frames in a background tab, so write the
            // real figure once regardless: a half-counted number would be wrong data.
            setTimeout(settle, delay + duration + 100);
        });
    })();
</script>
