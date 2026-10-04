<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">{{ $record->student->name }}</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('records.print', $record) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-printer"></i> {{ __('Print') }}
                </a>
                <a href="{{ route('records.edit', $record) }}" class="btn btn-sm btn-outline-secondary">{{ __('Edit') }}</a>
                <form method="POST" action="{{ route('records.destroy', $record) }}" onsubmit="return confirm('Delete this record? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <x-danger-button>{{ __('Delete') }}</x-danger-button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">{{ __('Class') }}</dt>
                <dd class="col-sm-9">{{ $record->student->class ?? '—' }}</dd>

                <dt class="col-sm-3">{{ __('Presenting Issue') }}</dt>
                <dd class="col-sm-9">
                    <span class="badge text-bg-light border">{{ $record->issue_type_label }}</span>
                </dd>

                <dt class="col-sm-3">{{ __('Category') }}</dt>
                <dd class="col-sm-9">{{ $record->category_label }}</dd>

                <dt class="col-sm-3">{{ __('Session Date') }}</dt>
                <dd class="col-sm-9">{{ $record->session_date->format('d M Y') }}</dd>

                <dt class="col-sm-3">{{ __('Session Notes') }}</dt>
                <dd class="col-sm-9" style="white-space: pre-wrap;">{{ $record->content }}</dd>

                <dt class="col-sm-3">{{ __('Last Updated') }}</dt>
                <dd class="col-sm-9 text-muted small">{{ $record->updated_at->format('d M Y, H:i') }}</dd>
            </dl>
        </div>
    </div>

    <a href="{{ route('records.index') }}" class="btn btn-link mt-2 ps-0">&larr; {{ __('Back to records') }}</a>
</x-app-layout>
