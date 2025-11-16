<?php
// app/Services/StudentService.php

namespace App\Services;

use App\Models\Student;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentService
{
    public function getPaginatedStudents(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Student::query();

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['class'])) {
            $query->where('class', $filters['class']);
        }

        if (!empty($filters['section'])) {
            $query->where('section', $filters['section']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('class')
            ->orderBy('section')
            ->orderBy('roll_number')
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function createStudent(array $data, $photoFile = null): Student
    {
        if ($photoFile) {
            $data['photo'] = $this->storePhoto($photoFile);
        }

        return Student::create($data);
    }

    public function updateStudent(Student $student, array $data, $photoFile = null): Student
    {
        if ($photoFile) {
            // Delete old photo if exists
            if ($student->photo) {
                Storage::disk('public')->delete($student->photo);
            }
            $data['photo'] = $this->storePhoto($photoFile);
        }

        $student->update($data);
        return $student->fresh();
    }

    public function deleteStudent(Student $student): bool
    {
        // Delete photo if exists
        if ($student->photo) {
            Storage::disk('public')->delete($student->photo);
        }

        return $student->delete();
    }

    public function getStudentsByClassSection(string $class, string $section)
    {
        return Student::where('class', $class)
            ->where('section', $section)
            ->where('is_active', true)
            ->orderBy('roll_number')
            ->orderBy('name')
            ->get();
    }

    public function getStudentAttendance(Student $student, array $filters = []): array
    {
        $query = $student->attendances()->with('recordedBy');

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

    public function bulkImportStudents($file): array
    {
        // This is a simplified version. In a real application, you would use a package like Maatwebsite/Laravel-Excel
        $results = [
            'successful' => 0,
            'failed' => 0,
            'errors' => []
        ];

        // Implementation for CSV/Excel import would go here
        // For now, return a placeholder response
        $results['errors'][] = 'Bulk import feature not implemented yet';

        return $results;
    }

    private function storePhoto($photoFile): string
    {
        $filename = 'student_' . Str::random(20) . '.' . $photoFile->getClientOriginalExtension();
        return $photoFile->storeAs('students', $filename, 'public');
    }
}
