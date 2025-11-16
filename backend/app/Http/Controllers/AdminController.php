<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;

class AdminController extends Controller
{
    public function getAllUsers(Request $request): JsonResponse
    {
        try {
            $search = $request->get('search');
            $role = $request->get('role');
            $perPage = $request->get('per_page', 10);

            $users = User::when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
                ->when($role, function ($query, $role) {
                    $query->where('role', $role);
                })
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $users,
                    'total' => $users->total()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch users', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load users'
            ], 500);
        }
    }

    public function createUser(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'phone' => 'nullable|string|max:20',
                'password' => ['required', Rules\Password::defaults()],
                'role' => 'required|in:admin,teacher,parent',
                'is_active' => 'sometimes|boolean'
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'is_active' => $request->is_active ?? true,
            ]);

            Log::info('User created by admin', ['admin_id' => $request->user()->id, 'user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create user', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create user'
            ], 500);
        }
    }

    public function updateUser(Request $request, User $user): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
                'phone' => 'sometimes|nullable|string|max:20',
                'password' => ['sometimes', Rules\Password::defaults()],
                'role' => 'sometimes|in:admin,teacher,parent',
                'is_active' => 'sometimes|boolean'
            ]);

            $updateData = $request->only(['name', 'email', 'phone', 'role', 'is_active']);

            if ($request->has('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            Log::info('User updated by admin', ['admin_id' => $request->user()->id, 'user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update user', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update user'
            ], 500);
        }
    }

    public function deleteUser(User $user): JsonResponse
    {
        try {
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account'
                ], 403);
            }

            $user->delete();

            Log::info('User deleted by admin', ['admin_id' => auth()->id(), 'user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete user', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user'
            ], 500);
        }
    }

    public function getSystemStats(): JsonResponse
    {
        try {
            $totalUsers = User::count();
            $adminUsers = User::where('role', 'admin')->count();
            $teacherUsers = User::where('role', 'teacher')->count();
            $parentUsers = User::where('role', 'parent')->count();

            $activeUsers = User::active()->count();
            $recentUsers = User::where('created_at', '>=', now()->subDays(7))->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_users' => $totalUsers,
                    'admin_users' => $adminUsers,
                    'teacher_users' => $teacherUsers,
                    'parent_users' => $parentUsers,
                    'active_users' => $activeUsers,
                    'recent_users' => $recentUsers
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch system stats', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load system statistics'
            ], 500);
        }
    }
}
