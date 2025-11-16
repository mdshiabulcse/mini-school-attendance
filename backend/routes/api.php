<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::get('/check-auth', [AuthController::class, 'checkAuth']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    // Dashboard routes
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [DashboardController::class, 'getStats']);
        Route::get('/recent-activities', [DashboardController::class, 'getRecentActivities']);
        Route::get('/monthly-chart', [DashboardController::class, 'getMonthlyChart']);
        Route::get('/class-performance', [DashboardController::class, 'getClassPerformance']);
        Route::get('/upcoming-events', [DashboardController::class, 'getUpcomingEvents']);
    });

    // Student routes
    Route::middleware('teacher')->group(function () {
        Route::apiResource('students', StudentController::class);
        Route::get('/students/{student}/attendance', [StudentController::class, 'getAttendance']);
        Route::get('/students/class/{class}/section/{section}', [StudentController::class, 'getByClassSection']);
        Route::post('/students/bulk-import', [StudentController::class, 'bulkImport']);
    });

    // Attendance routes
    Route::middleware('teacher')->group(function () {
        Route::post('/attendance/bulk', [AttendanceController::class, 'recordBulk']);
        Route::get('/attendance/today', [AttendanceController::class, 'getTodayAttendance']);
        Route::get('/attendance/monthly-report', [AttendanceController::class, 'getMonthlyReport']);
        Route::get('/attendance/class/{class}/section/{section}', [AttendanceController::class, 'getClassAttendance']);
        Route::get('/attendance/student/{studentId}', [AttendanceController::class, 'getStudentAttendance']);
        Route::get('/attendance/daily-report', [AttendanceController::class, 'getDailyReport']);
        Route::get('/download-monthly', [\App\Http\Controllers\ReportController::class, 'downloadMonthlyReport']);
        Route::get('/months', [\App\Http\Controllers\ReportController::class, 'getReportMonths']);
    });

    // Admin routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/users', [AdminController::class, 'getAllUsers']);
        Route::post('/users', [AdminController::class, 'createUser']);
        Route::put('/users/{user}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser']);
        Route::get('/system-stats', [AdminController::class, 'getSystemStats']);
    });
});
