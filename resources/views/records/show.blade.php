@php
    $categoryTone = [
        \App\Models\CounsellingRecord::CATEGORY_ACADEMIC => 1,
        \App\Models\CounsellingRecord::CATEGORY_BEHAVIOURAL => 2,
        \App\Models\CounsellingRecord::CATEGORY_EMOTIONAL_WELFARE => 3,
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('Counselling Record') }}</h2>
    </x-slot>

    <x-page-head :title="$record->student->name"
                 :subtitle="trim(($record->student->class ?? __('No class recorded')).' · '.$record->session_date->format('l, j F Y'))"
                 icon="bi-journal-text" tone="blue">
        <x-slot name="stats">
            <span class="sn-tag">
                <span class="sn-tag-dot viz-tone-{{ $categoryTone[$record->category] ?? 4 }}"></span>
                {{ $record->issue_type_label }}
            </span>
            <span class="sn-pill sn-pill-slate">{{ $record->category_label }}</span>
        </x-slot>

        <x-slot name="actions">
            <a href="{{ route('records.print', $record) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-printer me-1"></i>{{ __('Print') }}
            </a>
            <a href="{{ route('records.edit', $record) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i>{{ __('Edit') }}
            </a>
            <form method="POST" action="{{ route('records.destroy', $record) }}"
                  onsubmit="return confirm('{{ __('Delete this record? This cannot be undone.') }}');">
                @csrf
                @method('DELETE')
                <x-danger-button class="btn-sm">{{ __('Delete') }}</x-danger-button>
            </form>
        </x-slot>
    </x-page-head>

    <div class="sn-record-grid">
        <div class="card shadow-sm">
            <div class="card-body">
                <h3 class="h6 mb-3">{{ __('Session Notes') }}</h3>
                <div class="sn-note-body">{{ $record->content }}</div>
            </div>
        </div>

        <div class="d-flex flex-column gap-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="sn-meta-list">
                        <div class="sn-meta-item">
                            <span class="sn-meta-label">{{ __('Student') }}</span>
                            <span class="sn-meta-value">{{ $record->student->name }}</span>
                        </div>
                        <div class="sn-meta-item">
                            <span class="sn-meta-label">{{ __('Class') }}</span>
                            <span class="sn-meta-value">{{ $record->student->class ?? '—' }}</span>
                        </div>
                        <div class="sn-meta-item">
                            <span class="sn-meta-label">{{ __('Session Date') }}</span>
                            <span class="sn-meta-value">{{ $record->session_date->format('d M Y') }}</span>
                        </div>
                        <div class="sn-meta-item">
                            <span class="sn-meta-label">{{ __('Presenting Issue') }}</span>
                            <span class="sn-meta-value">{{ $record->issue_type_label }}</span>
                        </div>
                        <div class="sn-meta-item">
                            <span class="sn-meta-label">{{ __('Last Updated') }}</span>
                            <span class="sn-meta-value">{{ $record->updated_at->format('d M Y, H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sn-confidential">
                <i class="bi bi-shield-lock"></i>
                <span>{{ __('This note is encrypted at rest and readable only by you. Printing it takes it outside those controls, and the print is recorded in the audit log.') }}</span>
            </div>
        </div>
    </div>

    <a href="{{ route('records.index') }}" class="btn btn-link mt-3 ps-0">
        <i class="bi bi-arrow-left me-1"></i>{{ __('Back to records') }}
    </a>
</x-app-layout>
