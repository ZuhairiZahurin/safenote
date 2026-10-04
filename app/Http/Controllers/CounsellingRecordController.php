<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CounsellingRecord;
use App\Models\Student;
use App\Support\SchoolClasses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CounsellingRecordController extends Controller
{
    /**
     * Records list, scoped to the logged-in counsellor, with search-by-name
     * and filter-by-category (FR: Search and Filter Records).
     */
    public function index(Request $request)
    {
        $query = CounsellingRecord::with('student')
            ->where('counsellor_id', Auth::id());

        if ($search = $request->string('search')->trim()->value()) {
            $query->whereHas('student', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }

        if ($category = $request->string('category')->trim()->value()) {
            $query->where('category', $category);
        }

        if ($issueType = $request->string('issue_type')->trim()->value()) {
            $query->where('issue_type', $issueType);
        }

        $records = $query->latest('session_date')->paginate(10)->withQueryString();

        return view('records.index', [
            'records' => $records,
            'search' => $search,
            'category' => $category,
            'issueType' => $issueType,
        ]);
    }

    public function create()
    {
        return view('records.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_name' => ['required', 'string', 'max:255'],
            'student_class' => ['nullable', Rule::in(SchoolClasses::all())],
            'issue_type' => ['required', Rule::in(array_keys(CounsellingRecord::ISSUE_TYPES))],
            'session_date' => ['required', 'date'],
            'content' => ['required', 'string'],
        ]);

        $student = Student::resolve($validated['student_name'], $validated['student_class'] ?? null, Auth::id());

        $record = CounsellingRecord::create([
            'student_id' => $student->id,
            'counsellor_id' => Auth::id(),
            // Broad category is derived from the issue type so the two cannot diverge.
            'category' => CounsellingRecord::ISSUE_TYPES[$validated['issue_type']]['category'],
            'issue_type' => $validated['issue_type'],
            'session_date' => $validated['session_date'],
            'content' => $validated['content'],
        ]);

        return redirect()->route('records.show', $record)->with('status', 'Record created.');
    }

    public function show(CounsellingRecord $record)
    {
        $this->authorizeOwner($record);

        return view('records.show', ['record' => $record->load('student')]);
    }

    /**
     * Printer-friendly copy for the counsellor's physical case file. Opening it
     * is audit logged, since a printed page leaves the system's access control.
     */
    public function print(CounsellingRecord $record)
    {
        $this->authorizeOwner($record);

        AuditLog::record('record_printed', "Opened print view of record #{$record->id} for student #{$record->student_id}");

        return view('records.print', ['record' => $record->load(['student', 'counsellor'])]);
    }

    public function edit(CounsellingRecord $record)
    {
        $this->authorizeOwner($record);

        return view('records.edit', ['record' => $record->load('student')]);
    }

    public function update(Request $request, CounsellingRecord $record)
    {
        $this->authorizeOwner($record);

        $validated = $request->validate([
            'issue_type' => ['required', Rule::in(array_keys(CounsellingRecord::ISSUE_TYPES))],
            'session_date' => ['required', 'date'],
            'content' => ['required', 'string'],
        ]);

        $validated['category'] = CounsellingRecord::ISSUE_TYPES[$validated['issue_type']]['category'];

        $record->update($validated);

        return redirect()->route('records.show', $record)->with('status', 'Record updated.');
    }

    public function destroy(CounsellingRecord $record)
    {
        $this->authorizeOwner($record);

        $record->delete();

        return redirect()->route('records.index')->with('status', 'Record deleted.');
    }

    private function authorizeOwner(CounsellingRecord $record): void
    {
        abort_unless($record->counsellor_id === Auth::id(), 403);
    }
}
