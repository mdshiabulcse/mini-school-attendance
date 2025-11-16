<?php
// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => 'teacher',
                'is_active' => true
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            Log::info('New user registered', ['user_id' => $user->id, 'email' => $user->email]);

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'role' => $user->role
                    ],
                    'token' => $token
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Registration failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ], 500);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid login credentials'
                ], 401);
            }

            $user = User::where('email', $request->email)->active()->firstOrFail();

            $abilities = $user->isAdmin() ? ['admin'] : ($user->isTeacher() ? ['teacher'] : ['parent']);

            $token = $user->createToken('auth_token', $abilities)->plainTextToken;

            // Update last login
            $user->update(['last_login_at' => now()]);

            Log::info('User logged in', ['user_id' => $user->id, 'email' => $user->email]);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'role' => $user->role,
                        'avatar' => $user->avatar
                    ],
                    'token' => $token
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Login failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Login failed. Please try again.'
            ], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            Log::info('User logged out', ['user_id' => $request->user()->id]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully logged out'
            ]);

        } catch (\Exception $e) {
            Log::error('Logout failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Logout failed'
            ], 500);
        }
    }

    public function checkAuth(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'authenticated' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'avatar' => $user->avatar
                ]
            ]
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'phone' => 'sometimes|nullable|string|max:20',
                'avatar' => 'sometimes|nullable|string'
            ]);

            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'role' => $user->role,
                        'avatar' => $user->avatar
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Profile update failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Profile update failed'
            ], 500);
        }
    }
}
