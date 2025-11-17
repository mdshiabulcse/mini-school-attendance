<?php
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

        if (!$teacher) {
            $this->command->error('No teacher user found. Please run UserSeeder first.');
            return;
        }

        $students = Student::all();

        if ($students->isEmpty()) {
            $this->command->error('No students found. Please run StudentSeeder first.');
            return;
        }

        $startDate = Carbon::now()->subDays(30); // Last 30 days
        $endDate = Carbon::now();

        $attendanceCount = 0;

        foreach ($students as $student) {
            $currentDate = $startDate->copy();

            while ($currentDate <= $endDate) {
                // Skip weekends (Saturday and Sunday)
                if (!$currentDate->isWeekend()) {
                    $status = $this->getRandomAttendanceStatus();

                    // Check if attendance already exists for this student and date
                    $existingAttendance = Attendance::where('student_id', $student->id)
                        ->whereDate('date', $currentDate->format('Y-m-d'))
                        ->first();

                    if (!$existingAttendance) {
                        Attendance::create([
                            'student_id' => $student->id,
                            'date' => $currentDate->format('Y-m-d'),
                            'status' => $status,
                            'note' => $this->getAttendanceNote($status),
                            'recorded_by' => $teacher->id,
                            'created_at' => $currentDate,
                            'updated_at' => $currentDate,
                        ]);

                        $attendanceCount++;
                    }
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
