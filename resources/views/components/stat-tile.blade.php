@props([
    'label',
    'value',
    'note' => null,
    'icon' => 'bi-bar-chart',
    'tone' => 'blue',      // blue | amber | green | red | slate
    'change' => null,      // whole number: positive rises, negative falls
    'href' => null,
    'count' => true,       // animate the figure up from zero
])

@php
    $classes = 'sn-stat sn-stat-'.$tone.($href ? ' sn-stat-link' : '');
@endphp

@if ($href)
    <a href="{{ $href }}" class="{{ $classes }}">
@else
    <div class="{{ $classes }}">
@endif

    <div class="sn-stat-head">
        <span class="sn-stat-label">{{ $label }}</span>
        <span class="sn-stat-icon"><i class="bi {{ $icon }}"></i></span>
    </div>

    {{-- The final figure is in the markup, so it is correct without JavaScript. --}}
    <div class="sn-stat-value {{ $count ? 'viz-count' : '' }}" @if ($count) data-value="{{ $value }}" @endif>{{ $value }}</div>

    <div class="sn-stat-foot">
        @if (! is_null($change))
            <span class="sn-trend sn-trend-{{ $change > 0 ? 'up' : ($change < 0 ? 'down' : 'flat') }}">
                <i class="bi {{ $change > 0 ? 'bi-arrow-up-short' : ($change < 0 ? 'bi-arrow-down-short' : 'bi-dash') }}"></i>{{ abs($change) }}
            </span>
        @endif
        @if ($note)
            <span class="sn-stat-note">{{ $note }}</span>
        @endif
    </div>

@if ($href)
    </a>
@else
    </div>
@endif
