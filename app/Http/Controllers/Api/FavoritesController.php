<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoritesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $favorites = Favorite::where('user_id', $request->user()->id)
            ->when($request->listing_type, fn($q, $type) => $q->where('listing_type', $type))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($favorites);
    }

    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'listing_id' => 'required|uuid',
            'listing_type' => 'required|string|in:stays,vehicles,events,properties,sme_products',
        ]);

        $existing = Favorite::where('user_id', $request->user()->id)
            ->where('listing_id', $validated['listing_id'])
            ->where('listing_type', $validated['listing_type'])
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['favorited' => false]);
        }

        Favorite::create([
            'user_id' => $request->user()->id,
            'listing_id' => $validated['listing_id'],
            'listing_type' => $validated['listing_type'],
        ]);

        return response()->json(['favorited' => true], 201);
    }

    public function check(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'listing_id' => 'required|uuid',
            'listing_type' => 'required|string',
        ]);

        $exists = Favorite::where('user_id', $request->user()->id)
            ->where('listing_id', $validated['listing_id'])
            ->where('listing_type', $validated['listing_type'])
            ->exists();

        return response()->json(['favorited' => $exists]);
    }
}
