<?php

namespace App\Http\Controllers;

use App\Models\CounsellingRecord;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('dashboard', [
            'user' => $user,
            'stats' => $user->isCounsellor() ? $this->counsellorStats($user) : null,
            'teacherStats' => $user->isTeacher() ? $this->teacherStats($user) : null,
            'adminStats' => $user->isAdmin() ? $this->adminStats() : null,
        ]);
    }

    /**
     * Caseload statistics for the signed-in counsellor, scoped to their own
     * records only — a counsellor never sees another counsellor's caseload.
     */
    private function counsellorStats(User $user): array
    {
        $base = fn () => CounsellingRecord::where('counsellor_id', $user->id);

        $total = $base()->count();

        $issueCounts = $base()
            ->selectRaw('issue_type, COUNT(*) as total')
            ->groupBy('issue_type')
            ->pluck('total', 'issue_type');

        // Every known issue type appears, so a zero-count problem area is visible
        // rather than silently missing from the chart.
        $byIssue = collect(CounsellingRecord::ISSUE_TYPES)
            ->map(fn ($meta, $key) => [
                'key' => $key,
                'label' => $meta['label'],
                'category' => $meta['category'],
                'count' => (int) ($issueCounts[$key] ?? 0),
            ])
            ->values()
            ->sortByDesc('count')
            ->values();

        if ($unspecified = (int) ($issueCounts[null] ?? 0)) {
            $byIssue->push([
                'key' => null,
                'label' => 'Unspecified',
                'category' => null,
                'count' => $unspecified,
            ]);
        }

        $categoryCounts = $base()
            ->selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        $byCategory = collect(CounsellingRecord::CATEGORIES)
            ->map(fn ($label, $key) => [
                'key' => $key,
                'label' => $label,
                'count' => (int) ($categoryCounts[$key] ?? 0),
            ])
            ->values();

        return [
            'total' => $total,
            'thisMonth' => $base()->whereBetween('session_date', [
                now()->startOfMonth(), now()->endOfMonth(),
            ])->count(),
            'students' => $base()->distinct('student_id')->count('student_id'),
            'topIssue' => $byIssue->first(fn ($i) => $i['count'] > 0),
            'byIssue' => $byIssue,
            'maxIssueCount' => (int) $byIssue->max('count'),
            'byCategory' => $byCategory,
            'monthly' => $this->monthlyTrend($user),
        ];
    }

    /**
     * Session counts for the last six months, oldest first.
     */
    private function monthlyTrend(User $user): array
    {
        $start = now()->copy()->startOfMonth()->subMonths(5);

        // Grouped in PHP rather than with a driver-specific date function
        // (DATE_FORMAT / strftime), so this works on MySQL and SQLite alike.
        $counts = CounsellingRecord::where('counsellor_id', $user->id)
            ->where('session_date', '>=', $start)
            ->pluck('session_date')
            ->groupBy(fn ($date) => $date->format('Y-m'))
            ->map->count();

        $months = [];

        for ($i = 0; $i < 6; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');

            $months[] = [
                'label' => $month->format('M'),
                'full' => $month->format('F Y'),
                'count' => (int) ($counts[$key] ?? 0),
            ];
        }

        return $months;
    }

    /**
     * Referral statistics for a teacher, scoped to their own submissions.
     * Deliberately contains no counselling record data of any kind.
     */
    private function teacherStats(User $user): array
    {
        $base = fn () => Referral::where('teacher_id', $user->id);

        $statusCounts = $base()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $byStatus = collect(Referral::STATUSES)
            ->map(fn ($label, $key) => [
                'key' => $key,
                'label' => $label,
                'count' => (int) ($statusCounts[$key] ?? 0),
            ])
            ->values();

        $issueCounts = $base()
            ->selectRaw('issue_type, COUNT(*) as total')
            ->groupBy('issue_type')
            ->orderByDesc('total')
            ->pluck('total', 'issue_type');

        $byIssue = collect($issueCounts)
            ->map(fn ($count, $key) => [
                'label' => CounsellingRecord::issueTypeLabel($key),
                'count' => (int) $count,
            ])
            ->values();

        return [
            'total' => $base()->count(),
            'pending' => (int) ($statusCounts[Referral::STATUS_PENDING] ?? 0),
            'inReview' => (int) ($statusCounts[Referral::STATUS_IN_REVIEW] ?? 0),
            'closed' => (int) ($statusCounts[Referral::STATUS_CLOSED] ?? 0),
            'students' => $base()->distinct('student_id')->count('student_id'),
            'byStatus' => $byStatus,
            'byIssue' => $byIssue,
            'maxIssueCount' => (int) ($byIssue->max('count') ?? 0),
        ];
    }

    private function adminStats(): array
    {
        return [
            'users' => User::count(),
            'locked' => User::whereNotNull('locked_until')
                ->where('locked_until', '>', Carbon::now())
                ->count(),
        ];
    }
}
