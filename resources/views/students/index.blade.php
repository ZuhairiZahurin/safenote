<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('Student Profiles') }}</h2>
    </x-slot>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('students.index') }}" class="row g-2">
                <div class="col-sm-8">
                    <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search by student name...">
                </div>
                <div class="col-sm-4">
                    <button type="submit" class="btn btn-outline-secondary w-100">{{ __('Search') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="alert alert-light border small">
        <i class="bi bi-info-circle"></i>
        @if ($class)
            {{ __('Showing the students of your class, :class. Teachers can view basic student profile information only; confidential counselling notes are not accessible from this role.', ['class' => $class]) }}
        @else
            {{ __('No class has been assigned to your account yet, so no student profiles are shown. Please ask the administrator to assign your class.') }}
        @endif
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0">
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
                            <td>{{ $student->name }}</td>
                            <td>{{ $student->class ?? '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('students.show', $student) }}" class="btn btn-sm btn-outline-primary">{{ __('View') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">{{ $class ? __('No students found in your class.') : __('No class assigned to your account.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $students->links() }}
    </div>
</x-app-layout>
