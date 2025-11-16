<?php
// app/Console/Commands/GenerateAttendanceReport.php

namespace App\Console\Commands;

use App\Models\AttendanceSummary;
use App\Models\Student;
use App\Services\AttendanceSummaryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateAttendanceReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:generate-report
                            {month : The month in Y-m format (e.g., 2024-01)}
                            {class? : Optional class filter}
                            {--format=json : Output format (json, csv)}
                            {--save-file : Save the report to a file}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Generate monthly attendance report for students';

    /**
     * Execute the console command.
     */
    public function handle(AttendanceSummaryService $attendanceSummaryService): int
    {
        $month = $this->argument('month');
        $class = $this->argument('class');
        $format = $this->option('format');
        $saveFile = $this->option('save-file');

        // Validate month format
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $this->error('Invalid month format. Please use Y-m format (e.g., 2024-01)');
            return Command::FAILURE;
        }

        $this->info("Generating attendance report for {$month}" . ($class ? " (Class: {$class})" : ''));

        try {
            // Generate attendance summaries first
            $this->info('Generating attendance summaries...');
            $attendanceSummaryService->generateMonthlySummary($month);

            // Get the report data
            $reportData = $this->getReportData($month, $class);

            if (empty($reportData)) {
                $this->warn('No attendance data found for the specified criteria.');
                return Command::SUCCESS;
            }

            // Output or save the report
            if ($saveFile) {
                $filename = $this->saveReportToFile($reportData, $month, $class, $format);
                $this->info("Report saved to: {$filename}");
            } else {
                $this->displayReport($reportData, $format);
            }

            $this->info('Report generated successfully!');
            $this->info("Total records: " . count($reportData));

            Log::info('Attendance report generated', [
                'month' => $month,
                'class' => $class,
                'format' => $format,
                'records' => count($reportData)
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to generate report: ' . $e->getMessage());
            Log::error('Attendance report generation failed', [
                'month' => $month,
                'class' => $class,
                'error' => $e->getMessage()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Get report data from attendance summaries
     */
    private function getReportData(string $month, ?string $class): array
    {
        $query = AttendanceSummary::with('student')
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

    /**
     * Display report in console
     */
    private function displayReport(array $data, string $format): void
    {
        if ($format === 'json') {
            $this->line(json_encode($data, JSON_PRETTY_PRINT));
            return;
        }

        // CSV format in console (table)
        $headers = [
            'Student ID', 'Name', 'Class', 'Section', 'Roll No',
            'Total Days', 'Present', 'Absent', 'Late', 'Half Day', 'Percentage', 'Performance'
        ];

        $rows = array_map(function ($item) {
            return [
                $item['student_id'],
                $item['name'],
                $item['class'],
                $item['section'],
                $item['roll_number'] ?? 'N/A',
                $item['total_days'],
                $item['present_days'],
                $item['absent_days'],
                $item['late_days'],
                $item['half_days'],
                $item['attendance_percentage'] . '%',
                ucfirst($item['performance'])
            ];
        }, $data);

        $this->table($headers, $rows);
    }

    /**
     * Save report to file
     */
    private function saveReportToFile(array $data, string $month, ?string $class, string $format): string
    {
        $filename = "attendance-report-{$month}" . ($class ? "-class-{$class}" : '') . ".{$format}";
        $filepath = "reports/{$filename}";

        if ($format === 'json') {
            $content = json_encode($data, JSON_PRETTY_PRINT);
        } else {
            $content = $this->convertToCsv($data);
        }

        Storage::put($filepath, $content);

        return Storage::path($filepath);
    }

    /**
     * Convert data to CSV format
     */
    private function convertToCsv(array $data): string
    {
        if (empty($data)) {
            return '';
        }

        $headers = array_keys($data[0]);
        $csv = implode(',', $headers) . "\n";

        foreach ($data as $row) {
            $csv .= implode(',', array_map(function ($value) {
                    // Escape commas and quotes in values
                    $value = str_replace('"', '""', $value);
                    return '"' . $value . '"';
                }, $row)) . "\n";
        }

        return $csv;
    }
}
