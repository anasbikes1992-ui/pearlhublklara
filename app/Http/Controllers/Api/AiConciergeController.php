<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AiConciergeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiConciergeController extends Controller
{
    public function __construct(protected AiConciergeService $concierge) {}

    public function query(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => 'required|string|max:2000',
            'context' => 'nullable|array',
            'session_id' => 'nullable|string|uuid',
        ]);

        $result = $this->concierge->ask(
            $request->user()->id,
            $data['message'],
            $data['session_id'] ?? null,
            $data['context'] ?? []
        );

        return response()->json($result);
    }
}
