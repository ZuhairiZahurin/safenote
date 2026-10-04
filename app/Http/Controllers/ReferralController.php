<?php

namespace App\Http\Controllers;

use App\Models\CounsellingRecord;
use App\Models\Referral;
use App\Models\Student;
use App\Support\SchoolClasses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Teacher-side referrals.
 *
 * A teacher may submit a referral and track the STATUS of their own referrals,
 * but never sees any counselling note that results from it — the information
 * flow is deliberately one-way.
 */
class ReferralController extends Controller
{
    public function index(Request $request)
    {
        $query = Referral::with('student')->where('teacher_id', Auth::id());

        if ($status = $request->string('status')->trim()->value()) {
            $query->where('status', $status);
        }

        return view('referrals.index', [
            'referrals' => $query->latest()->paginate(10)->withQueryString(),
            'status' => $status,
        ]);
    }

    public function create()
    {
        return view('referrals.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_name' => ['required', 'string', 'max:255'],
            'student_class' => ['nullable', Rule::in(SchoolClasses::all())],
            'issue_type' => ['required', Rule::in(array_keys(CounsellingRecord::ISSUE_TYPES))],
            'urgency' => ['required', Rule::in(array_keys(Referral::URGENCIES))],
            'notes' => ['required', 'string', 'max:2000'],
        ]);

        $student = Student::resolve($validated['student_name'], $validated['student_class'] ?? null, Auth::id());

        Referral::create([
            'student_id' => $student->id,
            'teacher_id' => Auth::id(),
            'issue_type' => $validated['issue_type'],
            'urgency' => $validated['urgency'],
            'notes' => $validated['notes'],
            'status' => Referral::STATUS_PENDING,
        ]);

        return redirect()->route('referrals.index')
            ->with('status', 'Referral submitted to the counselling unit.');
    }

    /**
     * A teacher may re-read their own submission and see its status, but the
     * view deliberately exposes no counselling record content.
     */
    public function show(Referral $referral)
    {
        abort_unless($referral->teacher_id === Auth::id(), 403);

        return view('referrals.show', ['referral' => $referral->load('student')]);
    }
}
