<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        if ($action = $request->string('action')->trim()->value()) {
            $query->where('action', $action);
        }

        $logs = $query->paginate(25)->withQueryString();

        $actions = AuditLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('admin.audit-log.index', [
            'logs' => $logs,
            'actions' => $actions,
            'selectedAction' => $action,
        ]);
    }
}
