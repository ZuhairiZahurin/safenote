<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('Dashboard') }}</h2>
    </x-slot>

    <div class="card shadow-sm mb-4">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
            <p class="mb-0 d-flex align-items-center gap-2">
                <span class="fw-semibold">{{ __('Welcome back, :name.', ['name' => $user->name]) }}</span>
                <span class="sn-role-chip">{{ ucfirst($user->role) }}</span>
            </p>

            @if ($user->isCounsellor())
                <div class="d-flex gap-2">
                    <a href="{{ route('records.create') }}" class="btn btn-primary btn-sm">{{ __('+ New Record') }}</a>
                    <a href="{{ route('referral-inbox.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('Referral Inbox') }}</a>
                </div>
            @elseif ($user->isTeacher())
                <div class="d-flex gap-2">
                    <a href="{{ route('referrals.create') }}" class="btn btn-primary btn-sm">{{ __('+ Refer a Student') }}</a>
                    <a href="{{ route('students.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('Student Profiles') }}</a>
                </div>
            @endif
        </div>
    </div>

    @if ($user->isCounsellor())
        @include('dashboard.counsellor', ['stats' => $stats])
    @elseif ($user->isTeacher())
        @include('dashboard.teacher', ['teacherStats' => $teacherStats])
    @elseif ($user->isAdmin())
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">{{ __('Total Users') }}</div>
                        <div class="fs-3 fw-semibold">{{ $adminStats['users'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">{{ __('Locked Accounts') }}</div>
                        <div class="fs-3 fw-semibold">{{ $adminStats['locked'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column justify-content-center gap-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-primary btn-sm">{{ __('Manage Users') }}</a>
                        <a href="{{ route('admin.audit-log.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('View Audit Log') }}</a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-app-layout>
