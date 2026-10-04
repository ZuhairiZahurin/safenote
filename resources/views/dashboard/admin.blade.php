@php
    $total = max($adminStats['users'], 1);
    $needsAttention = $adminStats['locked'] + $adminStats['awaitingPasswordChange'];
@endphp

<div class="viz viz-animate">
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <x-stat-tile :label="__('Staff Accounts')" :value="$adminStats['users']"
                         :note="__('All roles')" icon="bi-people" tone="blue"
                         :href="route('admin.users.index')" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-tile :label="__('Active')" :value="$adminStats['active']"
                         :note="$adminStats['inactive'] > 0 ? trans_choice('{1} :count deactivated|[2,*] :count deactivated', $adminStats['inactive'], ['count' => $adminStats['inactive']]) : __('None deactivated')"
                         icon="bi-person-check" tone="green" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-tile :label="__('Locked Out')" :value="$adminStats['locked']"
                         :note="__('Failed sign-in lockout')" icon="bi-lock"
                         :tone="$adminStats['locked'] > 0 ? 'red' : 'slate'"
                         :href="route('admin.users.index')" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-tile :label="__('Must Change Password')" :value="$adminStats['awaitingPasswordChange']"
                         :note="__('Issued, not yet replaced')" icon="bi-key"
                         :tone="$adminStats['awaitingPasswordChange'] > 0 ? 'amber' : 'slate'" />
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h3 class="h6 mb-1">{{ __('Accounts by Role') }}</h3>
                    <p class="small text-muted mb-3">{{ __('Who holds access to the system.') }}</p>

                    <div class="viz-stack mb-3">
                        @foreach ($adminStats['byRole'] as $i => $role)
                            @if ($role['count'] > 0)
                                <div class="viz-stack-seg viz-tone-{{ $i + 1 }}"
                                     style="--w: {{ $role['count'] / $total * 100 }}%; --i: {{ $i }};"
                                     data-bs-toggle="tooltip"
                                     title="{{ $role['label'] }}: {{ $role['count'] }}"></div>
                            @endif
                        @endforeach
                    </div>

                    @foreach ($adminStats['byRole'] as $i => $role)
                        <div class="sn-legend-row" style="--i: {{ $i }};">
                            <span class="viz-swatch viz-tone-{{ $i + 1 }}"></span>
                            <span class="viz-legend-label flex-grow-1">{{ $role['label'] }}</span>
                            <span class="viz-legend-value">{{ $role['count'] }}</span>
                        </div>
                    @endforeach

                    <div class="sn-note mt-3">
                        <i class="bi bi-shield-lock"></i>
                        <span>{{ __('An administrator manages access only. Counselling records are never readable from this account.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="sn-card-head">
                        <div>
                            <h3 class="h6 mb-1">{{ __('Recent Activity') }}</h3>
                            <p class="small text-muted mb-0">{{ __('The latest entries in the audit log.') }}</p>
                        </div>
                        <a href="{{ route('admin.audit-log.index') }}" class="small text-decoration-none">{{ __('Full log') }}</a>
                    </div>

                    @forelse ($adminStats['recentActivity'] as $log)
                        <div class="sn-feed-row sn-feed-static">
                            <span class="sn-feed-dot viz-tone-{{ str_contains($log->action, 'lock') || str_contains($log->action, 'password') ? 2 : 1 }}"></span>
                            <span class="sn-feed-main">
                                <span class="sn-feed-title">{{ str_replace('_', ' ', ucfirst($log->action)) }}</span>
                                <span class="sn-feed-sub">{{ $log->description }}</span>
                            </span>
                            <span class="sn-feed-meta">
                                {{ $log->user?->name ?? __('System') }}<br>
                                <span class="text-muted">{{ $log->created_at->format('j M, H:i') }}</span>
                            </span>
                        </div>
                    @empty
                        <p class="sn-empty-note">{{ __('Nothing has been recorded yet.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @if ($needsAttention > 0)
        <div class="sn-alert-band mt-3">
            <i class="bi bi-exclamation-triangle"></i>
            <span>
                {{ trans_choice(
                    '{1} :count account needs your attention.|[2,*] :count accounts need your attention.',
                    $needsAttention, ['count' => $needsAttention]
                ) }}
            </span>
            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-dark ms-auto">{{ __('Review accounts') }}</a>
        </div>
    @endif
</div>
