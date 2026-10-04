<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ $student->name }}</h2>
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">{{ __('Class') }}</dt>
                <dd class="col-sm-9">{{ $student->class ?? '—' }}</dd>
            </dl>
        </div>
    </div>

    <div class="alert alert-light border small mt-3">
        <i class="bi bi-shield-lock"></i>
        {{ __('Counselling session notes for this student are confidential and are not shown to Teacher accounts.') }}
    </div>

    <a href="{{ route('students.index') }}" class="btn btn-link ps-0">&larr; {{ __('Back to student profiles') }}</a>
</x-app-layout>
