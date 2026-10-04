@php
    $roleTone = [
        \App\Models\User::ROLE_ADMIN => 1,
        \App\Models\User::ROLE_COUNSELLOR => 2,
        \App\Models\User::ROLE_TEACHER => 3,
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('User Accounts') }}</h2>
    </x-slot>

    <x-page-head :title="__('Staff Accounts')"
                 :subtitle="__('Who can sign in, and what each account may reach.')"
                 icon="bi-person-gear" tone="blue">
        <x-slot name="stats">
            <x-meter-chip :label="__('accounts')" :value="$users->total()" tone="blue" />
        </x-slot>

        <x-slot name="actions">
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>{{ __('New User') }}
            </a>
        </x-slot>
    </x-page-head>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table sn-table mb-0">
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Role') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>
                                <div class="sn-cell-primary">{{ $user->name }}</div>
                                <div class="sn-cell-sub">{{ $user->email }}</div>
                            </td>
                            <td>
                                <span class="sn-tag">
                                    <span class="sn-tag-dot viz-tone-{{ $roleTone[$user->role] ?? 4 }}"></span>
                                    {{ ucfirst($user->role) }}
                                </span>
                                @if ($user->class)
                                    <div class="sn-cell-sub mt-1">{{ $user->class }}</div>
                                @endif
                            </td>
                            <td>
                                @if ($user->isLocked())
                                    <span class="sn-pill sn-pill-red">{{ __('Locked') }}</span>
                                @elseif (! $user->is_active)
                                    <span class="sn-pill sn-pill-slate">{{ __('Deactivated') }}</span>
                                @else
                                    <span class="sn-pill sn-pill-green">{{ __('Active') }}</span>
                                @endif

                                @if ($user->must_change_password)
                                    <div class="mt-1">
                                        <span class="sn-pill sn-pill-amber">{{ __('Must change password') }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1 align-items-center">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-secondary">{{ __('Edit') }}</a>

                                    @if ($user->isLocked())
                                        <form method="POST" action="{{ route('admin.users.unlock', $user) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-warning">{{ __('Unlock') }}</button>
                                        </form>
                                    @endif

                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                {{ $user->is_active ? __('Deactivate') : __('Activate') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="sn-confidential mt-3">
        <i class="bi bi-shield-lock"></i>
        <span>{{ __('An administrator manages access only. No counselling record is readable from this account, and every change made here is written to the audit log.') }}</span>
    </div>

    <div class="mt-3">
        {{ $users->links() }}
    </div>
</x-app-layout>
