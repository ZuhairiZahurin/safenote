<nav class="navbar navbar-expand-sm sn-navbar" data-bs-theme="dark">
    <div class="container">
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <x-application-logo />
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                </li>

                @auth
                    @if (auth()->user()->isCounsellor())
                        <li class="nav-item">
                            <x-nav-link :href="route('records.index')" :active="request()->routeIs('records.*')">
                                {{ __('Records') }}
                            </x-nav-link>
                        </li>
                        <li class="nav-item">
                            <x-nav-link :href="route('referral-inbox.index')" :active="request()->routeIs('referral-inbox.*')">
                                {{ __('Referral Inbox') }}
                                @if ($pendingReferrals = \App\Models\Referral::where('status', 'pending')->count())
                                    <span class="badge text-bg-warning ms-1">{{ $pendingReferrals }}</span>
                                @endif
                            </x-nav-link>
                        </li>
                        <li class="nav-item">
                            <x-nav-link :href="route('reports.caseload')" :active="request()->routeIs('reports.*')">
                                {{ __('Reports') }}
                            </x-nav-link>
                        </li>
                    @endif

                    @if (auth()->user()->isTeacher())
                        <li class="nav-item">
                            <x-nav-link :href="route('students.index')" :active="request()->routeIs('students.*')">
                                {{ __('Student Profiles') }}
                            </x-nav-link>
                        </li>
                        <li class="nav-item">
                            <x-nav-link :href="route('referrals.index')" :active="request()->routeIs('referrals.*')">
                                {{ __('My Referrals') }}
                            </x-nav-link>
                        </li>
                    @endif

                    @if (auth()->user()->isAdmin())
                        <li class="nav-item">
                            <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                                {{ __('Users') }}
                            </x-nav-link>
                        </li>
                        <li class="nav-item">
                            <x-nav-link :href="route('admin.audit-log.index')" :active="request()->routeIs('admin.audit-log.*')">
                                {{ __('Audit Log') }}
                            </x-nav-link>
                        </li>
                    @endif
                @endauth
            </ul>

            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                        <span class="sn-avatar">{{ Str::of(Auth::user()->name)->explode(' ')->take(2)->map(fn ($p) => Str::substr($p, 0, 1))->implode('') }}</span>
                        {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">{{ __('Profile') }}</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">{{ __('Log Out') }}</button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
