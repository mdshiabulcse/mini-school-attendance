<?php
// app/Http/Middleware/TeacherMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeacherMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !($user->isTeacher() || $user->isAdmin())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Teacher or Admin access required.'
            ], 403);
        }

        return $next($request);
    }
}
