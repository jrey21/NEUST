<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function scan(Request $request)
    {
        $qrData = $request->input('qr_data');

        // Find student
        $student = Student::where('student_id', $qrData)->first();

        if (!$student) {
            return response()->json(['student' => null]);
        }

        $today = Carbon::now()->toDateString();
        $timeNow = Carbon::now()->toTimeString(); 

        $attendance = Attendance::where('student_id', $student->id)
                        ->where('date', $today)
                        ->first();

        if (!$attendance) {
            // First scan of the day (Time In)
            Attendance::create([
                'student_id' => $student->id,
                'date' => $today,
                'time_in' => $timeNow,
            ]);

            return response()->json([
                'student' => [
                    'name' => $student->name,
                    'year' => $student->year,
                    'course' => $student->course,
                    'status' => 'Checked In',
                    'alreadyScanned' => false
                ]
            ]);
        } elseif (!$attendance->time_out) {
            // Second scan of the day (Time Out)
            $attendance->update([
                'time_out' => $timeNow
            ]);

            return response()->json([
                'student' => [
                    'name' => $student->name,
                    'year' => $student->year,
                    'course' => $student->course,
                    'status' => 'Checked Out',
                    'alreadyScanned' => true
                ]
            ]);
        } else {
            // Already scanned in and out
            return response()->json([
                'student' => [
                    'name' => $student->name,
                    'year' => $student->year,
                    'course' => $student->course,
                    'status' => 'Checked Out',
                    'alreadyScanned' => true
                ]
            ]);
        }
    }
    // Get all scanned codes with student details
    public function getAllScannedCodes()
    {
        $attendances = Attendance::with('student')->get();

        return response()->json([
            'attendances' => $attendances->map(function ($attendance) {
                return [
                    'name' => $attendance->student->name,
                    'year' => $attendance->student->year,
                    'course' => $attendance->student->course,
                    'date' => $attendance->date,
                    'time_in' => $attendance->time_in,
                    'time_out' => $attendance->time_out,
                ];
            })
        ]);
    }

    //Count visitors today
    public function countVisitorsToday()
    {
        $today = now()->toDateString();
        $visitorCount = Attendance::where('date', $today)->count();

        return response()->json([
            'visitor_count' => $visitorCount 
        ]);
    }

    //Get total visitors
    public function getTotalVisitors()
    {
        $totalVisitors = Attendance::count();

        return response()->json([
            'total_visitors' => $totalVisitors
        ]);
    }

    //Count students without time out
    public function countStudentsWithoutTimeOut()
    {
        $count = Attendance::whereNull('time_out')->count();

        return response()->json([
            'currently_inside' => $count
        ]);
    }

    //Count scanned students by level
    public function countScannedStudentsByLevel()
    {
        $collegeCount = Attendance::whereHas('student', function ($query) {
            $query->whereIn('year', ['1st year', '2nd year', '3rd year', '4th year']);
        })->count();

        $jhsShsCount = Attendance::whereHas('student', function ($query) {
            $query->whereNotIn('year', ['1st year', '2nd year', '3rd year', '4th year']);
        })->count();

        return response()->json([
            'total_college' => $collegeCount,
            'total_JHS' => $jhsShsCount
        ]);
    }

    //Count scanned students weekly by level
    public function getWeeklyAttendance()
    {
        $startOfWeek = now()->startOfWeek()->toDateString();
        $endOfWeek = now()->endOfWeek()->toDateString();

        $attendance = Attendance::select(
                DB::raw("DAYNAME(date) as day"),
                'students.year',
                DB::raw("COUNT(*) as total")
            )
            ->join('students', 'attendances.student_id', '=', 'students.id')
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->groupBy('day', 'students.year')
            ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
            ->get();

        // Structure response for Vue
        $dailyAttendance = [
            'Monday' => ['College' => 0, 'JHS' => 0],
            'Tuesday' => ['College' => 0, 'JHS' => 0],
            'Wednesday' => ['College' => 0, 'JHS' => 0],
            'Thursday' => ['College' => 0, 'JHS' => 0],
            'Friday' => ['College' => 0, 'JHS' => 0],
            'Saturday' => ['College' => 0, 'JHS' => 0],
            'Sunday' => ['College' => 0, 'JHS' => 0],
        ];

        foreach ($attendance as $record) {
            $category = in_array($record->year, ['1st Year', '2nd Year', '3rd Year', '4th Year']) ? 'College' : 'JHS';
            $dailyAttendance[$record->day][$category] += $record->total;
        }

        return response()->json($dailyAttendance);
    }

    //Count scanned students daily by level
    public function countDailyVisitorsByLevel()
    {
        $today = now()->toDateString();

        $collegeCount = Attendance::where('date', $today)
            ->whereHas('student', function ($query) {
                $query->whereIn('year', ['1st year', '2nd year', '3rd year', '4th year']);
            })->count();

        $jhsShsCount = Attendance::where('date', $today)
            ->whereHas('student', function ($query) {
                $query->whereNotIn('year', ['1st year', '2nd year', '3rd year', '4th year']);
            })->count();

        return response()->json([
            'college' => $collegeCount,
            'jhs' => $jhsShsCount
        ]);
    }
}
