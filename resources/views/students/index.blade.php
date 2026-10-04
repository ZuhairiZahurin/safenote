<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('Student Profiles') }}</h2>
    </x-slot>

    <x-page-head :title="__('Student Profiles')"
                 :subtitle="$class
                    ? __('The students of your class. Basic details only.')
                    : __('No class has been assigned to your account yet.')"
                 icon="bi-people"
                 :tone="$class ? 'blue' : 'amber'">
        <x-slot name="stats">
            @if ($class)
                <x-meter-chip :label="__('students')" :value="$students->total()" tone="blue" />
                <span class="sn-pill sn-pill-slate">{{ $class }}</span>
            @endif
        </x-slot>
    </x-page-head>

    @if ($class)
        <div class="sn-filterbar mb-3">
            <form method="GET" action="{{ route('students.index') }}" class="row g-2">
                <div class="col-sm-8">
                    <div class="sn-field">
                        <input type="text" name="search" value="{{ $search }}" class="form-control"
                               placeholder="{{ __('Search by student name...') }}">
                        <i class="bi bi-search"></i>
                    </div>
                </div>
                <div class="col-sm-4">
                    <button type="submit" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-funnel me-1"></i>{{ __('Search') }}
                    </button>
                </div>
            </form>

            @if ($search)
                <div class="sn-active-filters">
                    <span>{{ __('Filtered by') }}</span>
                    <a class="sn-filter-chip" href="{{ route('students.index') }}">
                        &ldquo;{{ $search }}&rdquo; <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            @endif
        </div>
    @else
        <div class="sn-alert-band mb-3">
            <i class="bi bi-exclamation-triangle"></i>
            <span>{{ __('Ask the administrator to assign your class before student profiles will appear here.') }}</span>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table sn-table mb-0">
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Class') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        <tr>
                            <td><div class="sn-cell-primary">{{ $student->name }}</div></td>
                            <td class="sn-cell-sub">{{ $student->class ?? '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('students.show', $student) }}" class="btn btn-sm btn-outline-primary">{{ __('Open') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                <div class="sn-blank">
                                    <i class="bi bi-people"></i>
                                    <div class="sn-blank-title">
                                        {{ $class ? __('No students match.') : __('No class assigned.') }}
                                    </div>
                                    <p class="mb-0">
                                        {{ $class
                                            ? __('Nobody in your class matches that search.')
                                            : __('Your account is not tied to a class, so there is nothing to show.') }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="sn-confidential mt-3">
        <i class="bi bi-shield-lock"></i>
        <span>{{ __('A teacher sees basic profile details only. Counselling notes about these students are not readable from this account.') }}</span>
    </div>

    <div class="mt-3">
        {{ $students->links() }}
    </div>
</x-app-layout>
