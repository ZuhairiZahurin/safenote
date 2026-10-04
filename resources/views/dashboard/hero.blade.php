@php
    $hour = (int) now()->format('G');
    $greeting = $hour < 12 ? __('Good morning') : ($hour < 18 ? __('Good afternoon') : __('Good evening'));
    $firstName = str($user->name)->after('Madam ')->after('Mr ')->before(' bin ')->before(' binti ')->explode(' ')->first();
@endphp

<div class="sn-hero mb-4">
    <div class="sn-hero-text">
        <p class="sn-hero-greeting">{{ $greeting }}, {{ $firstName }}</p>
        <h2 class="sn-hero-name">{{ $user->name }}</h2>
        <div class="sn-hero-meta">
            <span class="sn-hero-chip">{{ ucfirst($user->role) }}</span>
            @if ($user->isTeacher() && $user->class)
                <span class="sn-hero-chip sn-hero-chip-soft">{{ $user->class }}</span>
            @endif
            <span class="sn-hero-date">{{ now()->format('l, j F Y') }}</span>
        </div>
    </div>

    <div class="sn-hero-actions">
        @if ($user->isCounsellor())
            <a href="{{ route('records.create') }}" class="btn btn-light btn-sm">
                <i class="bi bi-plus-lg me-1"></i>{{ __('New Record') }}
            </a>
            <a href="{{ route('referral-inbox.index') }}" class="btn btn-outline-light btn-sm">
                <i class="bi bi-inbox me-1"></i>{{ __('Referral Inbox') }}
            </a>
        @elseif ($user->isTeacher())
            <a href="{{ route('referrals.create') }}" class="btn btn-light btn-sm">
                <i class="bi bi-send me-1"></i>{{ __('Refer a Student') }}
            </a>
            <a href="{{ route('students.index') }}" class="btn btn-outline-light btn-sm">
                <i class="bi bi-people me-1"></i>{{ __('Student Profiles') }}
            </a>
        @elseif ($user->isAdmin())
            <a href="{{ route('admin.users.index') }}" class="btn btn-light btn-sm">
                <i class="bi bi-person-gear me-1"></i>{{ __('Manage Users') }}
            </a>
            <a href="{{ route('admin.audit-log.index') }}" class="btn btn-outline-light btn-sm">
                <i class="bi bi-list-check me-1"></i>{{ __('Audit Log') }}
            </a>
        @endif
    </div>
</div>
