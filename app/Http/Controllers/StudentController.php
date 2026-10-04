<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Read-only, basic student profile list for Teachers (name + class only),
     * scoped to the teacher's own class. Listing students outside that class
     * would reveal that a student has been in contact with the counselling unit.
     */
    public function index(Request $request)
    {
        $query = Student::inClassOf($request->user());

        if ($search = $request->string('search')->trim()->value()) {
            $query->where('name', 'like', "%{$search}%");
        }

        $students = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('students.index', [
            'students' => $students,
            'search' => $search,
            'class' => $request->user()->class,
        ]);
    }

    public function show(Request $request, Student $student)
    {
        abort_unless(Student::inClassOf($request->user())->whereKey($student->id)->exists(), 404);

        return view('students.show', ['student' => $student]);
    }
}
