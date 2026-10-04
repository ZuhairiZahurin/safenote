@props([
    'title',
    'subtitle' => null,
    'icon' => null,
    'tone' => 'blue',   // blue | amber | green | red | slate
])

{{-- The banded head used by every working page, so they read as one system
     without repeating the dashboard's full greeting band. --}}
<div class="sn-pagehead sn-pagehead-{{ $tone }} mb-3">
    @if ($icon)
        <span class="sn-pagehead-icon"><i class="bi {{ $icon }}"></i></span>
    @endif

    <div class="sn-pagehead-text">
        <h2 class="sn-pagehead-title">{{ $title }}</h2>
        @if ($subtitle)
            <p class="sn-pagehead-sub">{{ $subtitle }}</p>
        @endif
        @isset($stats)
            <div class="sn-pagehead-stats">{{ $stats }}</div>
        @endisset
    </div>

    @isset($actions)
        <div class="sn-pagehead-actions">{{ $actions }}</div>
    @endisset
</div>
