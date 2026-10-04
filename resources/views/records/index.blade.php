@php
    $categoryTone = [
        \App\Models\CounsellingRecord::CATEGORY_ACADEMIC => 1,
        \App\Models\CounsellingRecord::CATEGORY_BEHAVIOURAL => 2,
        \App\Models\CounsellingRecord::CATEGORY_EMOTIONAL_WELFARE => 3,
    ];
    $isFiltered = $search || $category || $issueType;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('Records') }}</h2>
    </x-slot>

    <x-page-head :title="__('Student Counselling Records')"
                 :subtitle="__('Your caseload. Only you can open these records.')"
                 icon="bi-folder2-open" tone="blue">
        <x-slot name="stats">
            <x-meter-chip :label="$isFiltered ? __('matching') : __('records')" :value="$records->total()" tone="blue" />
            @if ($isFiltered)
                <x-meter-chip :label="__('in the caseload')" :value="$caseloadTotal" />
            @endif
        </x-slot>

        <x-slot name="actions">
            <a href="{{ route('reports.caseload') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-printer me-1"></i>{{ __('Print Report') }}
            </a>
            <a href="{{ route('records.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>{{ __('New Record') }}
            </a>
        </x-slot>
    </x-page-head>

    <div class="sn-filterbar mb-3">
        <form method="GET" action="{{ route('records.index') }}" class="row g-2">
            <div class="col-lg-4 col-sm-6">
                <div class="sn-field">
                    <input type="text" name="search" value="{{ $search }}" class="form-control"
                           placeholder="{{ __('Search by student name...') }}">
                    <i class="bi bi-search"></i>
                </div>
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
            <div class="col-lg-2 col-sm-6">
                <button type="submit" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-funnel me-1"></i>{{ __('Filter') }}
                </button>
            </div>
        </form>

        @if ($isFiltered)
            <div class="sn-active-filters">
                <span>{{ __('Filtered by') }}</span>

                @if ($search)
                    <a class="sn-filter-chip" href="{{ route('records.index', array_filter(['category' => $category, 'issue_type' => $issueType])) }}">
                        &ldquo;{{ $search }}&rdquo; <i class="bi bi-x-lg"></i>
                    </a>
                @endif
                @if ($category)
                    <a class="sn-filter-chip" href="{{ route('records.index', array_filter(['search' => $search, 'issue_type' => $issueType])) }}">
                        {{ \App\Models\CounsellingRecord::categoryLabel($category) }} <i class="bi bi-x-lg"></i>
                    </a>
                @endif
                @if ($issueType)
                    <a class="sn-filter-chip" href="{{ route('records.index', array_filter(['search' => $search, 'category' => $category])) }}">
                        {{ \App\Models\CounsellingRecord::issueTypeLabel($issueType) }} <i class="bi bi-x-lg"></i>
                    </a>
                @endif

                <a href="{{ route('records.index') }}" class="ms-1 text-decoration-none">{{ __('Clear all') }}</a>
            </div>
        @endif
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table sn-table mb-0">
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
                            <td>
                                <div class="sn-cell-primary">{{ $record->student->name }}</div>
                                <div class="sn-cell-sub">{{ $record->student->class ?? '—' }}</div>
                            </td>
                            <td>
                                <span class="sn-tag">
                                    <span class="sn-tag-dot viz-tone-{{ $categoryTone[$record->category] ?? 4 }}"></span>
                                    {{ $record->issue_type_label }}
                                </span>
                            </td>
                            <td class="sn-cell-sub">{{ $record->category_label }}</td>
                            <td>
                                <div class="sn-cell-primary">{{ $record->session_date->format('d M Y') }}</div>
                                <div class="sn-cell-sub">{{ $record->session_date->diffForHumans() }}</div>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('records.show', $record) }}" class="btn btn-sm btn-outline-primary">{{ __('Open') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="sn-blank">
                                    <i class="bi bi-folder2-open"></i>
                                    <div class="sn-blank-title">
                                        {{ $isFiltered ? __('Nothing matches those filters.') : __('No records yet.') }}
                                    </div>
                                    <p class="mb-0">
                                        {{ $isFiltered
                                            ? __('Try widening the search, or clear the filters to see the whole caseload.')
                                            : __('Your first counselling record will appear here once you write it.') }}
                                    </p>
                                </div>
                            </td>
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
