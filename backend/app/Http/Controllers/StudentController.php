<?php
// app/Http/Controllers/StudentController.php

namespace App\Http\Controllers;

use App\Http\Resources\StudentResource;
use App\Models\Student;
use App\Services\StudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function __construct(private StudentService $studentService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        try {
            $perPage = $request->get('per_page', 15);
            $students = $this->studentService->getPaginatedStudents($request->all(), $perPage);

            return StudentResource::collection($students);

        } catch (\Exception $e) {
            Log::error('Failed to fetch students', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'student_id' => 'required|unique:students',
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:students',
                'class' => 'required|string',
                'section' => 'required|string',
                'roll_number' => 'nullable|string',
                'date_of_birth' => 'nullable|date',
                'gender' => 'required|in:male,female,other',
                'parent_name' => 'required|string|max:255',
                'parent_email' => 'nullable|email',
                'parent_phone' => 'required|string|max:20',
                'address' => 'nullable|string',
                'photo' => 'nullable|image|max:2048'
            ]);

            $student = $this->studentService->createStudent($validated, $request->file('photo'));

            Log::info('Student created', ['student_id' => $student->id, 'name' => $student->name]);

            return response()->json([
                'success' => true,
                'message' => 'Student created successfully',
                'data' => new StudentResource($student)
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create student', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create student'
            ], 500);
        }
    }

    public function show(Student $student): StudentResource
    {
        return new StudentResource($student->load(['attendances' => function ($query) {
            $query->latest()->limit(10);
        }]));
    }

    public function update(Request $request, Student $student): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|nullable|email|unique:students,email,' . $student->id,
                'class' => 'sometimes|required|string',
                'section' => 'sometimes|required|string',
                'roll_number' => 'sometimes|nullable|string',
                'date_of_birth' => 'sometimes|nullable|date',
                'gender' => 'sometimes|required|in:male,female,other',
                'parent_name' => 'sometimes|required|string|max:255',
                'parent_email' => 'sometimes|nullable|email',
                'parent_phone' => 'sometimes|required|string|max:20',
                'address' => 'sometimes|nullable|string',
                'photo' => 'sometimes|nullable|image|max:2048',
                'is_active' => 'sometimes|boolean'
            ]);

            $student = $this->studentService->updateStudent($student, $validated, $request->file('photo'));

            Log::info('Student updated', ['student_id' => $student->id, 'name' => $student->name]);

            return response()->json([
                'success' => true,
                'message' => 'Student updated successfully',
                'data' => new StudentResource($student)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update student', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update student'
            ], 500);
        }
    }

    public function destroy(Student $student): JsonResponse
    {
        try {
            $this->studentService->deleteStudent($student);

            Log::info('Student deleted', ['student_id' => $student->id, 'name' => $student->name]);

            return response()->json([
                'success' => true,
                'message' => 'Student deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete student', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete student'
            ], 500);
        }
    }

    public function getAttendance(Student $student, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'month' => 'sometimes|date_format:Y-m',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date'
            ]);

            $attendance = $this->studentService->getStudentAttendance($student, $request->all());

            return response()->json([
                'success' => true,
                'data' => $attendance
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch student attendance', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load attendance data'
            ], 500);
        }
    }

    public function getByClassSection($class, $section): AnonymousResourceCollection
    {
        try {
            $students = $this->studentService->getStudentsByClassSection($class, $section);

            return StudentResource::collection($students);

        } catch (\Exception $e) {
            Log::error('Failed to fetch class students', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function bulkImport(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,xlsx,xls'
            ]);

            $results = $this->studentService->bulkImportStudents($request->file('file'));

            return response()->json([
                'success' => true,
                'message' => 'Students imported successfully',
                'data' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk import failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
