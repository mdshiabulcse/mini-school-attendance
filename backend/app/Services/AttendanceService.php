<?php
// app/Services/AttendanceService.php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Student;
use App\Events\AttendanceRecorded;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function recordBulkAttendance(array $attendanceData, string $date, string $class, string $section, int $recordedBy): array
    {
        $results = [
            'successful' => 0,
            'failed' => 0,
            'errors' => []
        ];

        DB::transaction(function () use ($attendanceData, $date, $recordedBy, &$results) {
            foreach ($attendanceData as $data) {
                try {
                    $attendance = Attendance::updateOrCreate(
                        [
                            'student_id' => $data['student_id'],
                            'date' => $date
                        ],
                        [
                            'status' => $data['status'],
                            'note' => $data['note'] ?? null,
                            'recorded_by' => $recordedBy
                        ]
                    );

                    // event(new AttendanceRecorded($attendance));
                    $results['successful']++;

                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'student_id' => $data['student_id'],
                        'error' => $e->getMessage()
                    ];
                }
            }
        });

        $this->clearAttendanceCache();
        return $results;
    }

    public function getTodaySummary(): array
    {
        return Cache::remember('attendance_today_summary', 300, function () {
            $today = now()->format('Y-m-d');

            $totalStudents = Student::active()->count();
            $todayAttendance = Attendance::where('date', $today)->get();

            $present = $todayAttendance->where('status', 'present')->count();
            $absent = $todayAttendance->where('status', 'absent')->count();
            $late = $todayAttendance->where('status', 'late')->count();
            $halfDay = $todayAttendance->where('status', 'half_day')->count();
            $totalMarked = $present + $absent + $late + $halfDay;

            return [
                'total_students' => $totalStudents,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'half_day' => $halfDay,
                'not_marked' => $totalStudents - $totalMarked,
                'attendance_rate' => $totalStudents > 0 ?
                    round((($present + $late + ($halfDay * 0.5)) / $totalStudents) * 100, 2) : 0
            ];
        });
    }

    public function getMonthlyReport(string $month, ?string $class = null): array
    {
        $cacheKey = "attendance_report_{$month}_" . ($class ?? 'all');

        return Cache::remember($cacheKey, 3600, function () use ($month, $class) {
            $query = Student::with(['attendances' => function ($query) use ($month) {
                $query->whereMonth('date', date('m', strtotime($month)))
                    ->whereYear('date', date('Y', strtotime($month)));
            }]);

            if ($class) {
                $query->where('class', $class);
            }

            return $query->active()->get()->map(function ($student) use ($month) {
                $year = date('Y', strtotime($month));
                $monthNum = date('m', strtotime($month));

                return [
                    'student_id' => $student->student_id,
                    'name' => $student->name,
                    'class' => $student->class,
                    'section' => $student->section,
                    'roll_number' => $student->roll_number,
                    'attendance_percentage' => $student->getMonthlyAttendancePercentage($monthNum, $year),
                    'total_days' => $student->attendances->count(),
                    'present_days' => $student->attendances->where('status', 'present')->count(),
                    'absent_days' => $student->attendances->where('status', 'absent')->count(),
                    'late_days' => $student->attendances->where('status', 'late')->count(),
                    'half_days' => $student->attendances->where('status', 'half_day')->count(),
                ];
            })->toArray();
        });
    }

    public function getClassAttendance(string $class, string $section, string $date): array
    {
        $students = Student::where('class', $class)
            ->where('section', $section)
            ->with(['attendances' => function ($query) use ($date) {
                $query->whereDate('date', $date);
            }])
            ->active()
            ->orderBy('roll_number')
            ->get()
            ->toArray();

        return $students;
    }

    public function getStudentAttendance(int $studentId, array $filters = []): array
    {
        $query = Attendance::where('student_id', $studentId)
            ->with('recordedBy');

        if (isset($filters['month'])) {
            $query->whereYear('date', date('Y', strtotime($filters['month'])))
                ->whereMonth('date', date('m', strtotime($filters['month'])));
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('date', [$filters['start_date'], $filters['end_date']]);
        }

        $attendances = $query->orderBy('date', 'desc')
            ->paginate(30)
            ->toArray();

        return $attendances;
    }

    public function getDailyReport(string $date): array
    {
        $report = Attendance::with(['student', 'recordedBy'])
            ->whereDate('date', $date)
            ->get()
            ->groupBy('student.class')
            ->map(function ($classAttendances, $class) {
                return [
                    'class' => $class,
                    'total_students' => Student::where('class', $class)->active()->count(),
                    'present' => $classAttendances->where('status', 'present')->count(),
                    'absent' => $classAttendances->where('status', 'absent')->count(),
                    'late' => $classAttendances->where('status', 'late')->count(),
                    'half_day' => $classAttendances->where('status', 'half_day')->count(),
                    'attendance_rate' => Student::where('class', $class)->active()->count() > 0 ?
                        round(($classAttendances->whereIn('status', ['present', 'late'])->count() /
                                Student::where('class', $class)->active()->count()) * 100, 2) : 0
                ];
            })
            ->values()
            ->toArray();

        return $report;
    }

    private function clearAttendanceCache(): void
    {
        Cache::forget('attendance_today_summary');
        Cache::forget('dashboard_stats');
        Cache::forget('recent_activities');
        Cache::forget('class_performance');
    }
}
