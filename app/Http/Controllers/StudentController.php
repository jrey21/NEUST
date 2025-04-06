<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Student;
use Inertia\Inertia;


class StudentController extends Controller
{
    // Function to check if name is already exist
    public function checkName(Request $request)
    {
        $name = $request->name;
        $student = Student::where('name', $name)->first();
        if ($student) {
            return response()->json(['status' => 'error', 'message' => 'Name already exist']);
        } else {
            return response()->json(['status' => 'success', 'message' => 'Name is available']);
        }
    }

    //Store data into the database
    public function store(Request $request)
    {
       $request->validate([
            'student_id' => 'required',
            'name' => 'required',
            'course' => 'required',
            'year' => 'required',
            'adviser' => 'required',
        ]);

        Student::create($request->all());
        
        // session()->flash('message', 'Data has been added successfully!');

        return redirect()->route('students')->with('message', 'Student added successfully');
    }


    // Retrieve all the data from the database
    public function retrieve()
    {
        $student = Student::all(); 
        return response()->json($student);
    }

    // Function to count all the number of students
    public function countStudents()
    {
        $count = Student::count();
        return response()->json(['total_students' => $count]);
    }

    // Delete student
    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();
        return response()->json($student);
    }

    // Update student
    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        $student->update($request->all());
        return response()->json($student); // Return updated student data
    }

}
