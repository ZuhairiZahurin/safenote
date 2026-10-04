@props(['name', 'show' => false])

<div class="modal fade @if($show) show @endif" id="{{ $name }}" tabindex="-1" style="@if($show) display: block; @endif">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            {{ $slot }}
        </div>
    </div>
</div>
@if ($show)
    <div class="modal-backdrop fade show"></div>
@endif
