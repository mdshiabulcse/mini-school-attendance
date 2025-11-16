<?php
// database/seeders/AttendanceSeeder.php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $teacher = User::where('role', 'teacher')->first();
        $students = Student::all();

        $startDate = Carbon::now()->subDays(30); // Last 30 days
        $endDate = Carbon::now();

        $attendanceCount = 0;

        foreach ($students as $student) {
            $currentDate = $startDate->copy();

            while ($currentDate <= $endDate) {
                // Skip weekends (Saturday and Sunday)
                if (!$currentDate->isWeekend()) {
                    $status = $this->getRandomAttendanceStatus();

                    Attendance::create([
                        'student_id' => $student->id,
                        'date' => $currentDate->format('Y-m-d'),
                        'status' => $status,
                        'note' => $this->getAttendanceNote($status),
                        'recorded_by' => $teacher->id,
                    ]);

                    $attendanceCount++;
                }

                $currentDate->addDay();
            }
        }

        $this->command->info("Attendance records created: {$attendanceCount}");
        $this->command->info('Attendance data for last 30 days (excluding weekends) generated!');
    }

    private function getRandomAttendanceStatus(): string
    {
        $statuses = ['present', 'present', 'present', 'present', 'present', 'absent', 'late', 'half_day'];
        return $statuses[array_rand($statuses)];
    }

    private function getAttendanceNote(string $status): ?string
    {
        return match($status) {
            'absent' => 'Student was absent',
            'late' => 'Student arrived late',
            'half_day' => 'Student left early',
            default => null,
        };
    }
}
