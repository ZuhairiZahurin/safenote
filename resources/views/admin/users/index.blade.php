<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">{{ __('User Accounts') }}</h2>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> {{ __('New User') }}
            </a>
        </div>
    </x-slot>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Role') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td><span class="badge text-bg-light border">{{ ucfirst($user->role) }}</span></td>
                            <td>
                                @if ($user->isLocked())
                                    <span class="badge text-bg-danger">{{ __('Locked') }}</span>
                                @elseif (! $user->is_active)
                                    <span class="badge text-bg-secondary">{{ __('Deactivated') }}</span>
                                @else
                                    <span class="badge text-bg-success">{{ __('Active') }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-secondary">{{ __('Edit') }}</a>

                                    @if ($user->isLocked())
                                        <form method="POST" action="{{ route('admin.users.unlock', $user) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline-warning">{{ __('Unlock') }}</button>
                                        </form>
                                    @endif

                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline-danger">
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

    <div class="mt-3">
        {{ $users->links() }}
    </div>
</x-app-layout>
