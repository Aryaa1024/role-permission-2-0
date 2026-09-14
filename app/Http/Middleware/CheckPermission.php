<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $routeName = $request->route()?->getName();

        if (! $routeName) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Route name is not defined',
                'data' => null,
            ], 403);
        }

        $map = [
            'index' => 'index',
            'store' => 'create',
            'create' => 'create',
            'edit' => 'edit',
            'update' => 'edit',
            'destroy' => 'destroy',
            'delete' => 'destroy',
            'import' => 'import',
            'export' => 'export',
        ];

        $parts = explode('.', $routeName);
        $action = end($parts);

        if (! isset($map[$action])) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Permission mapping not defined',
                'data' => null,
            ], 403);
        }

        $permission = implode('.', array_slice($parts, 0, -1)).'.'.$map[$action];

        if (! $user?->can($permission)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized Access',
                'data' => null,
            ], 403);
        }

        return $next($request);
    }
}
