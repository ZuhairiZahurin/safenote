<?php

namespace App\Http\Controllers\Counsellor;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CounsellingRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Printable caseload report for a date range, e.g. a monthly report for the
 * principal. It never contains session notes, and student names are hidden
 * unless the counsellor explicitly asks for them.
 */
class ReportController extends Controller
{
    public function caseload(Request $request)
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'names' => ['nullable', 'boolean'],
        ]);

        $from = Carbon::parse($validated['from'] ?? now()->startOfMonth())->startOfDay();
        $to = Carbon::parse($validated['to'] ?? now()->endOfMonth())->endOfDay();
        $showNames = (bool) ($validated['names'] ?? false);

        // Selecting explicit columns keeps the encrypted notes out of memory entirely.
        $records = CounsellingRecord::with('student:id,name,class')
            ->where('counsellor_id', Auth::id())
            ->whereBetween('session_date', [$from, $to])
            ->orderBy('session_date')
            ->get(['id', 'student_id', 'category', 'issue_type', 'session_date']);

        $byCategory = collect(CounsellingRecord::CATEGORIES)
            ->map(fn ($label, $key) => [
                'label' => $label,
                'count' => $records->where('category', $key)->count(),
            ])
            ->values();

        $byIssue = collect(CounsellingRecord::ISSUE_TYPES)
            ->map(fn ($meta, $key) => [
                'label' => $meta['label'],
                'category' => CounsellingRecord::categoryLabel($meta['category']),
                'count' => $records->where('issue_type', $key)->count(),
            ])
            ->sortByDesc('count')
            ->values();

        AuditLog::record(
            'report_generated',
            "Generated caseload report for {$from->format('Y-m-d')} to {$to->format('Y-m-d')}"
                .($showNames ? ' (with student names)' : ' (names hidden)')
        );

        return view('reports.caseload', [
            'records' => $records,
            'from' => $from,
            'to' => $to,
            'showNames' => $showNames,
            'total' => $records->count(),
            'students' => $records->pluck('student_id')->unique()->count(),
            'byCategory' => $byCategory,
            'byIssue' => $byIssue,
        ]);
    }
}
