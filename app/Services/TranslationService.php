<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class TranslationService
{
    protected array $supportedLanguages = ['en', 'si', 'ta', 'hi', 'ar', 'zh', 'fr', 'de', 'es', 'ja'];

    public function translateText(string $text, string $from, string $to): string
    {
        if ($from === $to) {
            return $text;
        }

        $cacheKey = "translation:" . md5("{$text}:{$from}:{$to}");

        return Cache::remember($cacheKey, 86400, function () use ($text, $from, $to) {
            return $this->googleTranslate($text, $from, $to)
                ?? $this->libreTranslate($text, $from, $to)
                ?? $text;
        });
    }

    protected function googleTranslate(string $text, string $from, string $to): ?string
    {
        $apiKey = config('services.google_translate.key');
        if (!$apiKey) {
            return null;
        }

        try {
            $response = Http::post('https://translation.googleapis.com/language/translate/v2', [
                'q' => $text,
                'source' => $from,
                'target' => $to,
                'key' => $apiKey,
                'format' => 'text',
            ]);

            if ($response->successful()) {
                return $response->json('data.translations.0.translatedText');
            }
        } catch (\Exception $e) {
            report($e);
        }

        return null;
    }

    protected function libreTranslate(string $text, string $from, string $to): ?string
    {
        $baseUrl = config('services.libretranslate.url', 'https://libretranslate.com');

        try {
            $response = Http::post("{$baseUrl}/translate", [
                'q' => $text,
                'source' => $from,
                'target' => $to,
            ]);

            if ($response->successful()) {
                return $response->json('translatedText');
            }
        } catch (\Exception $e) {
            report($e);
        }

        return null;
    }

    public function transcribeVoice(string $audioPath): ?string
    {
        return $this->whisperTranscribe($audioPath)
            ?? $this->deepgramTranscribe($audioPath);
    }

    protected function whisperTranscribe(string $audioPath): ?string
    {
        $apiKey = config('services.openai.key');
        if (!$apiKey) {
            return null;
        }

        try {
            $response = Http::withToken($apiKey)
                ->attach('file', file_get_contents($audioPath), 'audio.webm')
                ->post('https://api.openai.com/v1/audio/transcriptions', [
                    'model' => 'whisper-1',
                ]);

            if ($response->successful()) {
                return $response->json('text');
            }
        } catch (\Exception $e) {
            report($e);
        }

        return null;
    }

    protected function deepgramTranscribe(string $audioPath): ?string
    {
        $apiKey = config('services.deepgram.key');
        if (!$apiKey) {
            return null;
        }

        try {
            $response = Http::withToken($apiKey)
                ->withBody(file_get_contents($audioPath), 'audio/webm')
                ->post('https://api.deepgram.com/v1/listen?model=nova-2');

            if ($response->successful()) {
                return $response->json('results.channels.0.alternatives.0.transcript');
            }
        } catch (\Exception $e) {
            report($e);
        }

        return null;
    }

    public function translateVoiceMessage(string $audioPath, string $targetLang): array
    {
        $transcription = $this->transcribeVoice($audioPath);
        if (!$transcription) {
            return ['original_text' => null, 'translated_text' => null];
        }

        $translated = $this->translateText($transcription, 'en', $targetLang);

        return [
            'original_text' => $transcription,
            'translated_text' => $translated,
        ];
    }

    public function isSupported(string $lang): bool
    {
        return in_array($lang, $this->supportedLanguages);
    }

    public function supportedLanguages(): array
    {
        return $this->supportedLanguages;
    }
}
