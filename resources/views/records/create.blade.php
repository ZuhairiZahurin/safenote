<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('New Record') }}</h2>
    </x-slot>

    <x-page-head :title="__('New Counselling Record')"
                 :subtitle="__('The note is encrypted as it is saved, and only you will be able to open it.')"
                 icon="bi-journal-plus" tone="blue" />

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('records.store') }}">
                @csrf

                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <x-input-label for="student_name" value="Student Name" />
                        <x-text-input id="student_name" name="student_name" type="text" :value="old('student_name')" required autofocus />
                        <x-input-error :messages="$errors->get('student_name')" />
                    </div>
                    <div class="col-md-4">
                        <x-input-label for="student_class" value="Class" />
                        <x-class-select name="student_class" :selected="old('student_class')" />
                        <x-input-error :messages="$errors->get('student_class')" />
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <x-input-label for="issue_type" value="Presenting Issue" />
                        <select id="issue_type" name="issue_type" class="form-select" required>
                            <option value="">Select an issue...</option>
                            @foreach (\App\Models\CounsellingRecord::issueTypesByCategory() as $categoryLabel => $issues)
                                <optgroup label="{{ $categoryLabel }}">
                                    @foreach ($issues as $key => $label)
                                        <option value="{{ $key }}" @selected(old('issue_type') === $key)>{{ $label }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <div class="form-text">{{ __('The case category is set automatically from the issue.') }}</div>
                        <x-input-error :messages="$errors->get('issue_type')" />
                    </div>
                    <div class="col-md-6">
                        <x-input-label for="session_date" value="Session Date" />
                        <x-text-input id="session_date" name="session_date" type="date" :value="old('session_date', now()->toDateString())" required />
                        <x-input-error :messages="$errors->get('session_date')" />
                    </div>
                </div>

                <div class="mb-3">
                    <x-input-label for="content" value="Session Notes" />
                    <textarea id="content" name="content" rows="8" class="form-control" required>{{ old('content') }}</textarea>
                    <div class="form-text">
                        <i class="bi bi-shield-lock"></i> {{ __('These notes are encrypted with AES-256 before being stored.') }}
                    </div>
                    <x-input-error :messages="$errors->get('content')" />
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('records.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('Cancel') }}</a>
                    <x-primary-button>{{ __('Save Record') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
