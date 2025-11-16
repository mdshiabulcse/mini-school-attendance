<?php
// app/Http/Controllers/AttendanceController.php

namespace App\Http\Controllers;

use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $attendanceService)
    {
    }

    public function recordBulk(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'date' => 'required|date',
                'class' => 'required|string',
                'section' => 'required|string',
                'attendances' => 'required|array',
                'attendances.*.student_id' => 'required|exists:students,id',
                'attendances.*.status' => 'required|in:present,absent,late,half_day',
                'attendances.*.note' => 'nullable|string'
            ]);

            $results = $this->attendanceService->recordBulkAttendance(
                $validated['attendances'],
                $validated['date'],
                $validated['class'],
                $validated['section'],
                $request->user()->id
            );

            Log::info('Bulk attendance recorded', [
                'recorded_by' => $request->user()->id,
                'date' => $validated['date'],
                'class' => $validated['class'],
                'section' => $validated['section'],
                'successful' => $results['successful'],
                'failed' => $results['failed']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Attendance recorded successfully',
                'data' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk attendance recording failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to record attendance'
            ], 500);
        }
    }

    public function getTodayAttendance(): JsonResponse
    {
        try {
            $summary = $this->attendanceService->getTodaySummary();

            return response()->json([
                'success' => true,
                'data' => $summary
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch today attendance', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load today\'s attendance'
            ], 500);
        }
    }

    public function getMonthlyReport(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'month' => 'required|date_format:Y-m',
                'class' => 'nullable|string'
            ]);

            $report = $this->attendanceService->getMonthlyReport(
                $request->month,
                $request->class
            );

            return response()->json([
                'success' => true,
                'data' => $report
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate monthly report', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report'
            ], 500);
        }
    }

    public function getClassAttendance($class, $section, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'date' => 'sometimes|date'
            ]);

            $date = $request->date ?? now()->format('Y-m-d');

            $attendance = $this->attendanceService->getClassAttendance($class, $section, $date);

            return response()->json([
                'success' => true,
                'data' => $attendance
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch class attendance', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load class attendance'
            ], 500);
        }
    }

    public function getStudentAttendance($studentId, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'month' => 'sometimes|date_format:Y-m',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date'
            ]);

            $attendance = $this->attendanceService->getStudentAttendance($studentId, $request->all());

            return response()->json([
                'success' => true,
                'data' => $attendance
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch student attendance', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load student attendance'
            ], 500);
        }
    }

    public function getDailyReport(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'date' => 'required|date'
            ]);

            $report = $this->attendanceService->getDailyReport($request->date);

            return response()->json([
                'success' => true,
                'data' => $report
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate daily report', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate daily report'
            ], 500);
        }
    }
}
