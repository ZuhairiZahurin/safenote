<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">{{ __('Student Counselling Records') }}</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('reports.caseload') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-printer"></i> {{ __('Print Report') }}
                </a>
                <a href="{{ route('records.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg"></i> {{ __('New Record') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('records.index') }}" class="row g-2">
                <div class="col-lg-4 col-sm-6">
                    <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search by student name...">
                </div>
                <div class="col-lg-3 col-sm-6">
                    <select name="category" class="form-select">
                        <option value="">{{ __('All categories') }}</option>
                        @foreach (\App\Models\CounsellingRecord::CATEGORIES as $key => $label)
                            <option value="{{ $key }}" @selected($category === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <select name="issue_type" class="form-select">
                        <option value="">{{ __('All issues') }}</option>
                        @foreach (\App\Models\CounsellingRecord::issueTypesByCategory() as $categoryLabel => $issues)
                            <optgroup label="{{ $categoryLabel }}">
                                @foreach ($issues as $key => $label)
                                    <option value="{{ $key }}" @selected($issueType === $key)>{{ $label }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-sm-6 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-secondary flex-fill">{{ __('Filter') }}</button>
                    @if ($search || $category || $issueType)
                        <a href="{{ route('records.index') }}" class="btn btn-link px-1" title="Clear filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
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
                        <th>{{ __('Presenting Issue') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th>{{ __('Session Date') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        <tr>
                            <td>{{ $record->student->name }}</td>
                            <td><span class="badge text-bg-light border">{{ $record->issue_type_label }}</span></td>
                            <td class="text-muted small">{{ $record->category_label }}</td>
                            <td>{{ $record->session_date->format('d M Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('records.show', $record) }}" class="btn btn-sm btn-outline-primary">{{ __('View') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">{{ __('No records found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $records->links() }}
    </div>
</x-app-layout>
