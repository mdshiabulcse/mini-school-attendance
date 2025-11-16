<?php
// app/Services/AttendanceSummaryService.php

namespace App\Services;

use App\Models\AttendanceSummary;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceSummaryService
{
    /**
     * Generate monthly attendance summary for all students
     */
    public function generateMonthlySummary(string $month): void
    {
        Log::info('Generating monthly attendance summary', ['month' => $month]);

        $students = Student::active()->get();

        $processed = 0;
        $errors = 0;

        foreach ($students as $student) {
            try {
                $this->generateStudentSummary($student, $month);
                $processed++;
            } catch (\Exception $e) {
                $errors++;
                Log::error('Failed to generate summary for student', [
                    'student_id' => $student->id,
                    'month' => $month,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Monthly attendance summary generation completed', [
            'month' => $month,
            'processed' => $processed,
            'errors' => $errors
        ]);
    }

    /**
     * Generate attendance summary for a specific student and month
     */
    public function generateStudentSummary(Student $student, string $month): void
    {
        $year = date('Y', strtotime($month));
        $monthNum = date('m', strtotime($month));

        $attendanceData = $student->attendances()
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNum)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $present = $attendanceData['present'] ?? 0;
        $absent = $attendanceData['absent'] ?? 0;
        $late = $attendanceData['late'] ?? 0;
        $halfDay = $attendanceData['half_day'] ?? 0;
        $total = $present + $absent + $late + $halfDay;

        $attendancePercentage = $total > 0 ?
            round(($present / $total) * 100, 2) : 0;

        AttendanceSummary::updateOrCreate(
            [
                'student_id' => $student->id,
                'month' => $month
            ],
            [
                'total_days' => $total,
                'present_days' => $present,
                'absent_days' => $absent,
                'late_days' => $late,
                'half_days' => $halfDay,
                'attendance_percentage' => $attendancePercentage
            ]
        );
    }

    /**
     * Get student attendance summary for a specific month
     */
    public function getStudentSummary(int $studentId, string $month): ?AttendanceSummary
    {
        return AttendanceSummary::where('student_id', $studentId)
            ->where('month', $month)
            ->first();
    }

    /**
     * Get class-wise attendance summary for a month
     */
    public function getClassSummary(string $month, string $class): array
    {
        return AttendanceSummary::with('student')
            ->whereHas('student', function ($query) use ($class) {
                $query->where('class', $class)->active();
            })
            ->where('month', $month)
            ->get()
            ->groupBy('student.section')
            ->map(function ($sectionSummaries, $section) {
                $totalStudents = $sectionSummaries->count();
                $avgAttendance = $sectionSummaries->avg('attendance_percentage');

                return [
                    'section' => $section,
                    'total_students' => $totalStudents,
                    'average_attendance' => round($avgAttendance, 2),
                    'excellent_performance' => $sectionSummaries->where('attendance_percentage', '>=', 90)->count(),
                    'good_performance' => $sectionSummaries->whereBetween('attendance_percentage', [75, 89])->count(),
                    'average_performance' => $sectionSummaries->whereBetween('attendance_percentage', [60, 74])->count(),
                    'poor_performance' => $sectionSummaries->where('attendance_percentage', '<', 60)->count(),
                ];
            })
            ->toArray();
    }

    /**
     * Delete attendance summaries for a specific month
     */
    public function deleteMonthlySummary(string $month): int
    {
        $deleted = AttendanceSummary::where('month', $month)->delete();

        Log::info('Deleted monthly attendance summaries', [
            'month' => $month,
            'deleted_count' => $deleted
        ]);

        return $deleted;
    }
}
