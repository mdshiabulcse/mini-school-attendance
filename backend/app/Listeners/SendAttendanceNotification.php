<?php
// app/Listeners/SendAttendanceNotification.php

namespace App\Listeners;

use App\Events\AttendanceRecorded;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAttendanceNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    public $queue = 'notifications';

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Handle the event.
     */
    public function handle(AttendanceRecorded $event): void
    {
        $attendance = $event->attendance;
        $student = $attendance->student;

        try {
            // Log the attendance recording
            Log::info('Attendance recorded notification', [
                'student_id' => $student->id,
                'student_name' => $student->name,
                'date' => $attendance->date->format('Y-m-d'),
                'status' => $attendance->status,
                'recorded_by' => $attendance->recordedBy->name
            ]);

            // Send email notification to parents if email exists and status is absent
            if ($this->shouldSendParentNotification($attendance)) {
                $this->sendParentNotification($attendance);
            }

            // Send notification to class teacher
            $this->sendTeacherNotification($attendance);

            // Update attendance summary
            $this->updateAttendanceSummary($attendance);

            Log::info('Attendance notifications sent successfully', [
                'attendance_id' => $attendance->id,
                'student_id' => $student->id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send attendance notifications', [
                'attendance_id' => $attendance->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Retry the job after a delay
            $this->release(60); // 1 minute delay
        }
    }

    /**
     * Determine if parent notification should be sent.
     */
    private function shouldSendParentNotification($attendance): bool
    {
        // Send notification only for absent students and if parent email exists
        return $attendance->status === 'absent' &&
            !empty($attendance->student->parent_email);
    }

    /**
     * Send notification to parent.
     */
    private function sendParentNotification($attendance): void
    {
        $student = $attendance->student;

        // In a real application, you would send an email or SMS
        // For now, we'll just log it
        Log::info('Parent notification would be sent', [
            'parent_email' => $student->parent_email,
            'student_name' => $student->name,
            'date' => $attendance->date->format('Y-m-d'),
            'status' => $attendance->status
        ]);

        // Example email sending (uncomment when mail is configured)
        /*
        Mail::to($student->parent_email)->send(new \App\Mail\StudentAbsenceNotification(
            $student,
            $attendance
        ));
        */
    }

    /**
     * Send notification to class teacher.
     */
    private function sendTeacherNotification($attendance): void
    {
        $student = $attendance->student;

        // Find class teacher (in a real app, you might have a teachers table)
        $classTeacher = User::where('role', 'teacher')
            ->first(); // You might want to filter by class in a real scenario

        if ($classTeacher) {
            Log::info('Teacher notification would be sent', [
                'teacher_id' => $classTeacher->id,
                'teacher_name' => $classTeacher->name,
                'student_name' => $student->name,
                'class' => $student->class,
                'section' => $student->section,
                'date' => $attendance->date->format('Y-m-d'),
                'status' => $attendance->status
            ]);

            // Example: Send in-app notification or email
            /*
            $classTeacher->notify(new \App\Notifications\AttendanceRecordedNotification($attendance));
            */
        }
    }

    /**
     * Update attendance summary for the month.
     */
    private function updateAttendanceSummary($attendance): void
    {
        $student = $attendance->student;
        $month = $attendance->date->format('Y-m');

        try {
            // This would typically be handled by a separate service
            // For now, we'll log the intention
            Log::info('Attendance summary update triggered', [
                'student_id' => $student->id,
                'month' => $month,
                'attendance_id' => $attendance->id
            ]);

            // In a real application, you might call a service like:
            // app(\App\Services\AttendanceSummaryService::class)->updateStudentSummary($student->id, $month);

        } catch (\Exception $e) {
            Log::error('Failed to update attendance summary', [
                'student_id' => $student->id,
                'month' => $month,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(AttendanceRecorded $event, \Throwable $exception): void
    {
        Log::error('Attendance notification job failed after all attempts', [
            'attendance_id' => $event->attendance->id,
            'student_id' => $event->attendance->student_id,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        // You might want to send an alert to administrators here
        // or implement a fallback notification mechanism
    }
}
