<?php
// app/Http/Controllers/ReportController.php

namespace App\Http\Controllers;

use App\Services\AttendanceSummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(private AttendanceSummaryService $attendanceSummaryService)
    {
    }

    /**
     * Download monthly attendance report
     */
    public function downloadMonthlyReport(Request $request): StreamedResponse
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
            'class' => 'nullable|string',
            'format' => 'required|in:json,csv'
        ]);

        $month = $request->month;
        $class = $request->class;
        $format = $request->format;

        // Generate the report first
        $this->attendanceSummaryService->generateMonthlySummary($month);

        // Get report data
        $reportData = $this->getReportData($month, $class);

        $filename = "attendance-report-{$month}" . ($class ? "-class-{$class}" : '') . ".{$format}";

        if ($format === 'json') {
            return $this->downloadJson($reportData, $filename);
        }

        return $this->downloadCsv($reportData, $filename);
    }

    private function getReportData(string $month, ?string $class): array
    {
        // Use the same logic as in the command
        $query = \App\Models\AttendanceSummary::with('student')
            ->where('month', $month);

        if ($class) {
            $query->whereHas('student', function ($q) use ($class) {
                $q->where('class', $class);
            });
        }

        return $query->get()
            ->map(function ($summary) {
                return [
                    'student_id' => $summary->student->student_id,
                    'name' => $summary->student->name,
                    'class' => $summary->student->class,
                    'section' => $summary->student->section,
                    'roll_number' => $summary->student->roll_number,
                    'total_days' => $summary->total_days,
                    'present_days' => $summary->present_days,
                    'absent_days' => $summary->absent_days,
                    'late_days' => $summary->late_days,
                    'half_days' => $summary->half_days,
                    'attendance_percentage' => $summary->attendance_percentage,
                    'performance' => $summary->performance
                ];
            })
            ->toArray();
    }

    private function downloadJson(array $data, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT);
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    private function downloadCsv(array $data, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($data) {
            $output = fopen('php://output', 'w');

            // Add headers
            if (!empty($data)) {
                fputcsv($output, array_keys($data[0]));
            }

            // Add data
            foreach ($data as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Get available report months
     */
    public function getReportMonths(): \Illuminate\Http\JsonResponse
    {
        $months = \App\Models\AttendanceSummary::select('month')
            ->distinct()
            ->orderBy('month', 'desc')
            ->pluck('month')
            ->toArray();

        return response()->json([
            'success' => true,
            'data' => $months
        ]);
    }
}
