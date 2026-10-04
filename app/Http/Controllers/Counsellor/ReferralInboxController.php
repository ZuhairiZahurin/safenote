<?php

namespace App\Http\Controllers\Counsellor;

use App\Http\Controllers\Controller;
use App\Models\CounsellingRecord;
use App\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Counsellor-side referral inbox: review referrals raised by teachers and
 * turn them into counselling records.
 */
class ReferralInboxController extends Controller
{
    public function index(Request $request)
    {
        $query = Referral::with(['student', 'teacher']);

        $status = $request->string('status')->trim()->value();

        if ($status) {
            $query->where('status', $status);
        }

        // Highest urgency first, then oldest — the queue a counsellor should work.
        $referrals = $query
            ->orderByRaw("CASE urgency WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->oldest()
            ->paginate(10)
            ->withQueryString();

        $counts = Referral::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('counsellor.referrals.index', [
            'referrals' => $referrals,
            'status' => $status,
            'counts' => $counts,
            'totalCount' => $counts->sum(),
            'pendingCount' => (int) ($counts[Referral::STATUS_PENDING] ?? 0),
            // The oldest case still waiting is the one most at risk of being forgotten.
            'longestWait' => Referral::where('status', Referral::STATUS_PENDING)->min('created_at'),
        ]);
    }

    public function show(Referral $referral)
    {
        return view('counsellor.referrals.show', [
            'referral' => $referral->load(['student', 'teacher', 'reviewer', 'counsellingRecord']),
        ]);
    }

    public function updateStatus(Request $request, Referral $referral)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(Referral::STATUSES))],
        ]);

        $referral->update([
            'status' => $validated['status'],
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('status', 'Referral status updated.');
    }

    /**
     * Create a counselling record from the referral and close it. The teacher
     * will see the status change, but never the record's content.
     */
    public function convert(Request $request, Referral $referral)
    {
        if ($referral->counselling_record_id) {
            return back()->with('status', 'This referral has already been converted.');
        }

        $validated = $request->validate([
            'session_date' => ['required', 'date'],
            'content' => ['required', 'string'],
        ]);

        $record = CounsellingRecord::create([
            'student_id' => $referral->student_id,
            'counsellor_id' => Auth::id(),
            'category' => CounsellingRecord::ISSUE_TYPES[$referral->issue_type]['category'],
            'issue_type' => $referral->issue_type,
            'session_date' => $validated['session_date'],
            'content' => $validated['content'],
        ]);

        $referral->update([
            'status' => Referral::STATUS_CLOSED,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'counselling_record_id' => $record->id,
        ]);

        return redirect()->route('records.show', $record)
            ->with('status', 'Counselling record created from referral.');
    }
}
