<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('Edit Record — ') }}{{ $record->student->name }}</h2>
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('records.update', $record) }}">
                @csrf
                @method('PUT')

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <x-input-label for="issue_type" value="Presenting Issue" />
                        <select id="issue_type" name="issue_type" class="form-select" required>
                            @foreach (\App\Models\CounsellingRecord::issueTypesByCategory() as $categoryLabel => $issues)
                                <optgroup label="{{ $categoryLabel }}">
                                    @foreach ($issues as $key => $label)
                                        <option value="{{ $key }}" @selected(old('issue_type', $record->issue_type) === $key)>{{ $label }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <div class="form-text">{{ __('The case category is set automatically from the issue.') }}</div>
                        <x-input-error :messages="$errors->get('issue_type')" />
                    </div>
                    <div class="col-md-6">
                        <x-input-label for="session_date" value="Session Date" />
                        <x-text-input id="session_date" name="session_date" type="date" :value="old('session_date', $record->session_date->toDateString())" required />
                        <x-input-error :messages="$errors->get('session_date')" />
                    </div>
                </div>

                <div class="mb-3">
                    <x-input-label for="content" value="Session Notes" />
                    <textarea id="content" name="content" rows="8" class="form-control" required>{{ old('content', $record->content) }}</textarea>
                    <x-input-error :messages="$errors->get('content')" />
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('records.show', $record) }}" class="btn btn-outline-secondary btn-sm">{{ __('Cancel') }}</a>
                    <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
