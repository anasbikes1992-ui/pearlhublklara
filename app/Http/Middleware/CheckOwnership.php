<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOwnership
{
    public function handle(Request $request, Closure $next, string $model, string $ownerField = 'provider_id'): Response
    {
        $modelClass = "App\\Models\\{$model}";
        $routeParam = strtolower($model);
        $record = $modelClass::find($request->route($routeParam));

        if (!$record) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        $profile = $request->user()->profile;
        if ($record->{$ownerField} !== $profile?->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
