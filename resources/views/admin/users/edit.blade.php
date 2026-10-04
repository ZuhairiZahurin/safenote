<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('Edit User') }}</h2>
    </x-slot>

    <x-page-head :title="$user->name"
                 :subtitle="$user->email"
                 icon="bi-person-gear"
                 :tone="$user->is_active ? 'blue' : 'slate'">
        <x-slot name="stats">
            <span class="sn-pill sn-pill-slate">{{ ucfirst($user->role) }}</span>
            @if ($user->isLocked())
                <span class="sn-pill sn-pill-red">{{ __('Locked') }}</span>
            @elseif (! $user->is_active)
                <span class="sn-pill sn-pill-slate">{{ __('Deactivated') }}</span>
            @else
                <span class="sn-pill sn-pill-green">{{ __('Active') }}</span>
            @endif
            @if ($user->must_change_password)
                <span class="sn-pill sn-pill-amber">{{ __('Must change password') }}</span>
            @endif
        </x-slot>
    </x-page-head>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <x-input-label for="name" value="Name" />
                    <x-text-input id="name" name="name" type="text" :value="old('name', $user->name)" required autofocus />
                    <x-input-error :messages="$errors->get('name')" />
                </div>

                <div class="mb-3">
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)" required />
                    <x-input-error :messages="$errors->get('email')" />
                </div>

                <div class="mb-3">
                    <x-input-label for="role" value="Role" />
                    <select id="role" name="role" class="form-select" required>
                        <option value="counsellor" @selected(old('role', $user->role) === 'counsellor')>Counsellor</option>
                        <option value="teacher" @selected(old('role', $user->role) === 'teacher')>Teacher</option>
                        <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
                    </select>
                    <x-input-error :messages="$errors->get('role')" />
                </div>

                <div class="mb-3">
                    <x-input-label for="class" value="Class (teachers only)" />
                    <x-class-select name="class" :selected="old('class', $user->class)" placeholder="No class assigned" />
                    <div class="form-text">{{ __('A teacher sees the student profiles of this class only. Leave blank for counsellors and admins.') }}</div>
                    <x-input-error :messages="$errors->get('class')" />
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('Cancel') }}</a>
                    <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    @if ($user->id !== auth()->id())
        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <h3 class="h6 mb-1">{{ __('Issue a New Password') }}</h3>
                <p class="small text-muted mb-3">
                    {{ __('Use this when :name has forgotten their password and has identified themselves to you in person. It also unlocks the account and signs them out of any device still logged in. Hand the password over directly and ask them to change it from their profile.', ['name' => $user->name]) }}
                </p>

                <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="row g-3">
                    @csrf
                    @method('PATCH')

                    <div class="col-md-5">
                        <x-input-label for="password" value="New password" />
                        <x-text-input id="password" name="password" type="text" autocomplete="off" required />
                        <div class="form-text">{{ __('At least 8 characters.') }}</div>
                        <x-input-error :messages="$errors->get('password')" />
                    </div>

                    <div class="col-md-5">
                        <x-input-label for="password_confirmation" value="Confirm new password" />
                        <x-text-input id="password_confirmation" name="password_confirmation" type="text" autocomplete="off" required />
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100"
                                onclick="return confirm('{{ __('Issue a new password for this user and sign them out everywhere?') }}')">
                            {{ __('Issue') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</x-app-layout>
