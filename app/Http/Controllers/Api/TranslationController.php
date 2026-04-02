<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    public function __construct(protected TranslationService $translationService) {}

    public function translate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text' => 'required|string|max:5000',
            'from' => 'required|string|max:5',
            'to' => 'required|string|max:5',
        ]);

        if (!$this->translationService->isSupported($validated['to'])) {
            return response()->json(['message' => 'Unsupported language'], 422);
        }

        $translated = $this->translationService->translateText(
            $validated['text'],
            $validated['from'],
            $validated['to']
        );

        return response()->json(['translated_text' => $translated]);
    }

    public function languages(): JsonResponse
    {
        return response()->json(['languages' => $this->translationService->supportedLanguages()]);
    }
}
