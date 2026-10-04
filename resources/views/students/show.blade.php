<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('Student Profile') }}</h2>
    </x-slot>

    <x-page-head :title="$student->name"
                 :subtitle="$student->class ?? __('No class recorded')"
                 icon="bi-person-badge" tone="blue" />

    <div class="sn-record-grid">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="sn-meta-list">
                    <div class="sn-meta-item">
                        <span class="sn-meta-label">{{ __('Name') }}</span>
                        <span class="sn-meta-value">{{ $student->name }}</span>
                    </div>
                    <div class="sn-meta-item">
                        <span class="sn-meta-label">{{ __('Class') }}</span>
                        <span class="sn-meta-value">{{ $student->class ?? '—' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="sn-confidential">
            <i class="bi bi-shield-lock"></i>
            <span>{{ __('Counselling session notes for this student are confidential under Akta Kaunselor 1998 and are not shown to teacher accounts.') }}</span>
        </div>
    </div>

    <a href="{{ route('students.index') }}" class="btn btn-link mt-3 ps-0">
        <i class="bi bi-arrow-left me-1"></i>{{ __('Back to student profiles') }}
    </a>
</x-app-layout>
