<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService)
    {
    }

    public function getStats(): JsonResponse
    {
        try {
            $stats = $this->dashboardService->getDashboardStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to fetch dashboard stats', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard statistics'
            ], 500);
        }
    }

    public function getRecentActivities(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);
            $activities = $this->dashboardService->getRecentActivities($limit);

            return response()->json([
                'success' => true,
                'data' => $activities
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to fetch recent activities', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load recent activities'
            ], 500);
        }
    }

    public function getMonthlyChart(Request $request): JsonResponse
    {
        try {
            $year = $request->get('year', date('Y'));
            $month = $request->get('month', date('m'));

            $chartData = $this->dashboardService->getMonthlyAttendanceChart($year, $month);

            return response()->json([
                'success' => true,
                'data' => $chartData
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to fetch monthly chart data', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load chart data'
            ], 500);
        }
    }

    public function getClassPerformance(): JsonResponse
    {
        try {
            $performance = $this->dashboardService->getClassWisePerformance();

            return response()->json([
                'success' => true,
                'data' => $performance
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to fetch class performance', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load class performance data'
            ], 500);
        }
    }

    public function getUpcomingEvents(): JsonResponse
    {
        try {
            $events = $this->dashboardService->getUpcomingEvents();

            return response()->json([
                'success' => true,
                'data' => $events
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to fetch upcoming events', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load upcoming events'
            ], 500);
        }
    }
}
