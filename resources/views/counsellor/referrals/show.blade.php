<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('Review Referral — ') }}{{ $referral->student->name }}</h2>
    </x-slot>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">{{ __('Student') }}</dt>
                        <dd class="col-sm-8">{{ $referral->student->name }} <span class="text-muted">({{ $referral->student->class ?? '—' }})</span></dd>

                        <dt class="col-sm-4">{{ __('Referred By') }}</dt>
                        <dd class="col-sm-8">{{ $referral->teacher->name ?? '—' }}</dd>

                        <dt class="col-sm-4">{{ __('Reason') }}</dt>
                        <dd class="col-sm-8">{{ $referral->issue_type_label }}</dd>

                        <dt class="col-sm-4">{{ __('Urgency') }}</dt>
                        <dd class="col-sm-8"><x-urgency-badge :urgency="$referral->urgency" :label="$referral->urgency_label" /></dd>

                        <dt class="col-sm-4">{{ __('Received') }}</dt>
                        <dd class="col-sm-8">{{ $referral->created_at->format('d M Y, H:i') }}</dd>

                        <dt class="col-sm-4">{{ __("Teacher's Observation") }}</dt>
                        <dd class="col-sm-8" style="white-space: pre-wrap;">{{ $referral->notes }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h3 class="h6 mb-3">{{ __('Status') }}</h3>
                    <p class="mb-3">
                        <span class="badge text-bg-{{ $referral->status_variant }}">{{ $referral->status_label }}</span>
                        @if ($referral->reviewed_at)
                            <span class="small text-muted ms-1">
                                {{ __('by') }} {{ $referral->reviewer->name ?? '—' }}, {{ $referral->reviewed_at->format('d M Y') }}
                            </span>
                        @endif
                    </p>

                    <form method="POST" action="{{ route('referral-inbox.status', $referral) }}" class="d-flex gap-2">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="form-select form-select-sm">
                            @foreach (\App\Models\Referral::STATUSES as $key => $label)
                                <option value="{{ $key }}" @selected($referral->status === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-primary-button>{{ __('Update') }}</x-primary-button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="h6 mb-1">{{ __('Create Counselling Record') }}</h3>

                    @if ($referral->counselling_record_id)
                        <p class="small text-muted mb-2">{{ __('A counselling record has already been created from this referral.') }}</p>
                        <a href="{{ route('records.show', $referral->counselling_record_id) }}" class="btn btn-outline-primary btn-sm">
                            {{ __('Open the record') }}
                        </a>
                    @else
                        <p class="small text-muted mb-3">{{ __('Record the session held following this referral. The teacher will see only that the referral is closed, never these notes.') }}</p>

                        <form method="POST" action="{{ route('referral-inbox.convert', $referral) }}">
                            @csrf

                            <div class="mb-3">
                                <x-input-label for="session_date" value="Session Date" />
                                <x-text-input id="session_date" name="session_date" type="date" :value="old('session_date', now()->toDateString())" required />
                                <x-input-error :messages="$errors->get('session_date')" />
                            </div>

                            <div class="mb-3">
                                <x-input-label for="content" value="Session Notes" />
                                <textarea id="content" name="content" rows="6" class="form-control" required>{{ old('content') }}</textarea>
                                <x-input-error :messages="$errors->get('content')" />
                            </div>

                            <x-primary-button>{{ __('Create Record & Close Referral') }}</x-primary-button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <a href="{{ route('referral-inbox.index') }}" class="btn btn-link ps-0 mt-2">&larr; {{ __('Back to inbox') }}</a>
</x-app-layout>
