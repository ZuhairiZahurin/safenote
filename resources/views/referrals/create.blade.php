<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('Refer a Student') }}</h2>
    </x-slot>

    <x-page-head :title="__('Refer a Student to the Counselling Unit')"
                 :subtitle="__('Describe what you have observed. The counsellor decides what happens next, and you will see the status only.')"
                 icon="bi-send" tone="blue" />

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('referrals.store') }}">
                @csrf

                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <x-input-label for="student_name" value="Student Name" />
                        <x-text-input id="student_name" name="student_name" type="text" :value="old('student_name')" required autofocus />
                        <x-input-error :messages="$errors->get('student_name')" />
                    </div>
                    <div class="col-md-4">
                        <x-input-label for="student_class" value="Class" />
                        <x-class-select name="student_class" :selected="old('student_class', auth()->user()->class)" />
                        <x-input-error :messages="$errors->get('student_class')" />
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <x-input-label for="issue_type" value="Reason for Referral" />
                        <select id="issue_type" name="issue_type" class="form-select" required>
                            <option value="">Select a reason...</option>
                            @foreach (\App\Models\CounsellingRecord::issueTypesByCategory() as $categoryLabel => $issues)
                                <optgroup label="{{ $categoryLabel }}">
                                    @foreach ($issues as $key => $label)
                                        <option value="{{ $key }}" @selected(old('issue_type') === $key)>{{ $label }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('issue_type')" />
                    </div>
                    <div class="col-md-4">
                        <x-input-label for="urgency" value="Urgency" />
                        <select id="urgency" name="urgency" class="form-select" required>
                            @foreach (\App\Models\Referral::URGENCIES as $key => $label)
                                <option value="{{ $key }}" @selected(old('urgency', 'medium') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('urgency')" />
                    </div>
                </div>

                <div class="mb-3">
                    <x-input-label for="notes" value="What have you observed?" />
                    <textarea id="notes" name="notes" rows="6" class="form-control" required
                              placeholder="Describe what you have noticed, e.g. repeated absence, sudden change in behaviour...">{{ old('notes') }}</textarea>
                    <div class="form-text">
                        <i class="bi bi-shield-lock"></i>
                        {{ __('Your observation is encrypted with AES-256 and visible only to the counselling unit.') }}
                    </div>
                    <x-input-error :messages="$errors->get('notes')" />
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('referrals.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('Cancel') }}</a>
                    <x-primary-button>{{ __('Submit Referral') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
