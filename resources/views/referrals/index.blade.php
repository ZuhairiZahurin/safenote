<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">{{ __('My Referrals') }}</h2>
            <a href="{{ route('referrals.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> {{ __('Refer a Student') }}
            </a>
        </div>
    </x-slot>

    <div class="alert alert-light border small">
        <i class="bi bi-shield-lock"></i>
        {{ __('You can see the progress of referrals you submitted. The counselling notes made by the counsellor remain confidential and are not shown here.') }}
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('referrals.index') }}" class="row g-2">
                <div class="col-sm-5">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach (\App\Models\Referral::STATUSES as $key => $label)
                            <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>{{ __('Student') }}</th>
                        <th>{{ __('Issue') }}</th>
                        <th>{{ __('Urgency') }}</th>
                        <th>{{ __('Submitted') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($referrals as $referral)
                        <tr>
                            <td>{{ $referral->student->name }}</td>
                            <td><span class="badge text-bg-light border">{{ $referral->issue_type_label }}</span></td>
                            <td><x-urgency-badge :urgency="$referral->urgency" :label="$referral->urgency_label" /></td>
                            <td class="small text-muted">{{ $referral->created_at->format('d M Y') }}</td>
                            <td><span class="badge text-bg-{{ $referral->status_variant }}">{{ $referral->status_label }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('referrals.show', $referral) }}" class="btn btn-sm btn-outline-primary">{{ __('View') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                {{ __('You have not referred any students yet.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $referrals->links() }}
    </div>
</x-app-layout>
