<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOwnership
{
    private const ALLOWED_MODELS = [
        'StaysListing' => \App\Models\StaysListing::class,
        'VehiclesListing' => \App\Models\VehiclesListing::class,
        'EventsListing' => \App\Models\EventsListing::class,
        'PropertiesListing' => \App\Models\PropertiesListing::class,
        'SmeBusiness' => \App\Models\SmeBusiness::class,
        'SmeProduct' => \App\Models\SmeProduct::class,
        'Booking' => \App\Models\Booking::class,
    ];

    public function handle(Request $request, Closure $next, string $model, string $ownerField = 'provider_id'): Response
    {
        if (!isset(self::ALLOWED_MODELS[$model])) {
            return response()->json(['message' => 'Invalid resource type'], 400);
        }

        $modelClass = self::ALLOWED_MODELS[$model];
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
