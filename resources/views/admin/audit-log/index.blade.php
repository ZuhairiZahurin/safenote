<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('Audit Log') }}</h2>
    </x-slot>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.audit-log.index') }}" class="row g-2">
                <div class="col-sm-4">
                    <select name="action" class="form-select" onchange="this.form.submit()">
                        <option value="">{{ __('All actions') }}</option>
                        @foreach ($actions as $action)
                            <option value="{{ $action }}" @selected($selectedAction === $action)>{{ str_replace('_', ' ', ucfirst($action)) }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0">
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
                            <td class="text-nowrap">{{ $log->created_at->format('d M Y, H:i') }}</td>
                            <td>{{ $log->user->name ?? '—' }}</td>
                            <td><span class="badge text-bg-light border">{{ str_replace('_', ' ', ucfirst($log->action)) }}</span></td>
                            <td class="small text-muted">{{ $log->description }}</td>
                            <td class="small text-muted">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">{{ __('No activity recorded yet.') }}</td>
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
