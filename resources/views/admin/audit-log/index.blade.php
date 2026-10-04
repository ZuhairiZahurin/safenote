@php
    // Security-relevant actions read differently from routine ones, so they are
    // marked. The label is always present, so the colour only reinforces it.
    $isSecurity = fn ($action) => (bool) preg_match('/lock|password|login|timeout|deactivat/i', $action);
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('Audit Log') }}</h2>
    </x-slot>

    <x-page-head :title="__('Audit Log')"
                 :subtitle="__('An append-only record of what happened and who did it. Entries cannot be edited or removed.')"
                 icon="bi-list-check" tone="slate">
        <x-slot name="stats">
            <x-meter-chip :label="$selectedAction ? __('matching entries') : __('entries')" :value="$logs->total()" />
        </x-slot>
    </x-page-head>

    <div class="sn-filterbar mb-3">
        <form method="GET" action="{{ route('admin.audit-log.index') }}" class="row g-2">
            <div class="col-sm-5">
                <select name="action" class="form-select" onchange="this.form.submit()">
                    <option value="">{{ __('All actions') }}</option>
                    @foreach ($actions as $action)
                        <option value="{{ $action }}" @selected($selectedAction === $action)>{{ str_replace('_', ' ', ucfirst($action)) }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        @if ($selectedAction)
            <div class="sn-active-filters">
                <span>{{ __('Filtered by') }}</span>
                <a class="sn-filter-chip" href="{{ route('admin.audit-log.index') }}">
                    {{ str_replace('_', ' ', ucfirst($selectedAction)) }} <i class="bi bi-x-lg"></i>
                </a>
            </div>
        @endif
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table sn-table mb-0">
                <thead>
                    <tr>
                        <th>{{ __('When') }}</th>
                        <th>{{ __('User') }}</th>
                        <th>{{ __('Action') }}</th>
                        <th>{{ __('Details') }}</th>
                        <th>{{ __('IP Address') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="text-nowrap">
                                <div class="sn-cell-primary">{{ $log->created_at->format('d M Y') }}</div>
                                <div class="sn-cell-sub">{{ $log->created_at->format('H:i') }}</div>
                            </td>
                            <td>
                                <div class="sn-cell-primary">{{ $log->user->name ?? __('System') }}</div>
                                <div class="sn-cell-sub">{{ $log->user?->role ? ucfirst($log->user->role) : '—' }}</div>
                            </td>
                            <td>
                                <span class="sn-tag">
                                    <span class="sn-tag-dot viz-tone-{{ $isSecurity($log->action) ? 2 : 1 }}"></span>
                                    {{ str_replace('_', ' ', ucfirst($log->action)) }}
                                </span>
                            </td>
                            <td class="sn-cell-sub">{{ $log->description }}</td>
                            <td class="sn-cell-sub text-nowrap">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="sn-blank">
                                    <i class="bi bi-list-check"></i>
                                    <div class="sn-blank-title">
                                        {{ $selectedAction ? __('No entries of that kind.') : __('Nothing recorded yet.') }}
                                    </div>
                                    <p class="mb-0">
                                        {{ $selectedAction
                                            ? __('Clear the filter to see everything that has been logged.')
                                            : __('Actions taken in SafeNote will appear here as they happen.') }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $logs->links() }}
    </div>
</x-app-layout>
