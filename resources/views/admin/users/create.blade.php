<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('New User Account') }}</h2>
    </x-slot>

    <x-page-head :title="__('New Staff Account')"
                 :subtitle="__('The password you set here is known to you, so the holder will be asked to replace it before anything else opens.')"
                 icon="bi-person-plus" tone="blue" />

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf

                <div class="mb-3">
                    <x-input-label for="name" value="Name" />
                    <x-text-input id="name" name="name" type="text" :value="old('name')" required autofocus />
                    <x-input-error :messages="$errors->get('name')" />
                </div>

                <div class="mb-3">
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" name="email" type="email" :value="old('email')" required />
                    <x-input-error :messages="$errors->get('email')" />
                </div>

                <div class="mb-3">
                    <x-input-label for="role" value="Role" />
                    <select id="role" name="role" class="form-select" required>
                        <option value="counsellor" @selected(old('role') === 'counsellor')>Counsellor</option>
                        <option value="teacher" @selected(old('role') === 'teacher')>Teacher</option>
                        <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                    </select>
                    <x-input-error :messages="$errors->get('role')" />
                </div>

                <div class="mb-3">
                    <x-input-label for="class" value="Class (teachers only)" />
                    <x-class-select name="class" :selected="old('class')" placeholder="No class assigned" />
                    <div class="form-text">{{ __('A teacher sees the student profiles of this class only. Leave blank for counsellors and admins.') }}</div>
                    <x-input-error :messages="$errors->get('class')" />
                </div>

                <div class="mb-3">
                    <x-input-label for="password" value="Temporary Password" />
                    <x-text-input id="password" name="password" type="password" required />
                    <x-input-error :messages="$errors->get('password')" />
                </div>

                <div class="mb-3">
                    <x-input-label for="password_confirmation" value="Confirm Password" />
                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" required />
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('Cancel') }}</a>
                    <x-primary-button>{{ __('Create Account') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
