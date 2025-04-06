<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScannedCodes;
use App\Models\Student;
use Inertia\Inertia;

class ScannedCodesController extends Controller
{
    public function scan(Request $request)
    {
        $qrData = $request->input('qr_data');
        $student = Student::where('student_id', $qrData)->first(); 

        if ($student) {
            return response()->json([
                'student' => [
                    'name' => $student->name,
                    'year' => $student->year,
                    'course' => $student->course,
                ]
            ]);
        }

        return response()->json(['message' => 'Invalid QR Code'], 404);
    }

    //retrieve scanned codes
    public function retrieve()
    {
        $codes = ScannedCodes::all();
        return response()->json($codes);
    }
}
