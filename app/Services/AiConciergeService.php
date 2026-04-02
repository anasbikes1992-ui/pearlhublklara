<?php

namespace App\Services;

use App\Models\AiConciergeLog;
use Illuminate\Support\Facades\Http;

class AiConciergeService
{
    public function ask(string $userId, string $query, ?string $sessionId = null, array $context = []): array
    {
        $startTime = microtime(true);

        // Try Anthropic first, then Grok fallback
        $result = $this->tryAnthropic($query, $context)
            ?? $this->tryGrok($query, $context)
            ?? ['response' => 'I apologize, but I am temporarily unable to process your request. Please try again shortly.', 'provider' => 'fallback', 'model' => 'none', 'tokens' => 0];

        $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);

        // Log the interaction
        AiConciergeLog::create([
            'user_id' => $userId,
            'query' => $query,
            'response' => $result['response'],
            'provider' => $result['provider'],
            'model' => $result['model'],
            'tokens_used' => $result['tokens'],
            'response_time_ms' => $responseTimeMs,
            'session_id' => $sessionId ?? \Illuminate\Support\Str::uuid()->toString(),
            'context' => $context,
        ]);

        return [
            'response' => $result['response'],
            'provider' => $result['provider'],
            'session_id' => $sessionId,
        ];
    }

    protected function tryAnthropic(string $query, array $context): ?array
    {
        $apiKey = config('services.anthropic.key');
        if (!$apiKey) {
            return null;
        }

        try {
            $messages = [['role' => 'user', 'content' => $this->buildPrompt($query, $context)]];

            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
            ])->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-sonnet-4-20250514',
                'max_tokens' => 1024,
                'messages' => $messages,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'response' => $data['content'][0]['text'] ?? '',
                    'provider' => 'anthropic',
                    'model' => 'claude-sonnet-4-20250514',
                    'tokens' => ($data['usage']['input_tokens'] ?? 0) + ($data['usage']['output_tokens'] ?? 0),
                ];
            }
        } catch (\Exception $e) {
            report($e);
        }

        return null;
    }

    protected function tryGrok(string $query, array $context): ?array
    {
        $apiKey = config('services.xai.key');
        if (!$apiKey) {
            return null;
        }

        try {
            $response = Http::withToken($apiKey)
                ->post('https://api.x.ai/v1/chat/completions', [
                    'model' => 'grok-beta',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are PearlHub AI Concierge, a helpful assistant for a Sri Lankan services marketplace covering stays, vehicles, events, properties, SME products, and taxi services.'],
                        ['role' => 'user', 'content' => $query],
                    ],
                    'max_tokens' => 1024,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'response' => $data['choices'][0]['message']['content'] ?? '',
                    'provider' => 'grok',
                    'model' => 'grok-beta',
                    'tokens' => $data['usage']['total_tokens'] ?? 0,
                ];
            }
        } catch (\Exception $e) {
            report($e);
        }

        return null;
    }

    protected function buildPrompt(string $query, array $context): string
    {
        $systemContext = "You are PearlHub AI Concierge, a helpful assistant for a Sri Lankan services marketplace. ";
        $systemContext .= "PearlHub covers: Stays (hotels/villas), Vehicles (rentals), Events (venues/services), ";
        $systemContext .= "Properties (real estate), SME Products (local businesses), and Taxi services. ";
        $systemContext .= "Help users find services, compare options, and make recommendations based on their needs. ";
        $systemContext .= "Be friendly, concise, and knowledgeable about Sri Lankan tourism and local services.";

        if (!empty($context)) {
            $systemContext .= "\n\nAdditional context: " . json_encode($context);
        }

        return $systemContext . "\n\nUser: " . $query;
    }
}
