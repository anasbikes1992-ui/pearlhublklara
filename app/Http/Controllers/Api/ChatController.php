<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(protected MessageService $messageService) {}

    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'listing_id' => 'required|uuid',
            'receiver_id' => 'required|uuid',
            'message' => 'required|string|max:2000',
            'original_lang' => 'nullable|string|max:5',
            'target_lang' => 'nullable|string|max:5',
        ]);

        $validated['sender_id'] = $request->user()->profile->id;
        $validated['channel'] = $validated['channel'] ?? null;

        $message = $this->messageService->send($validated);

        return response()->json(['data' => $message], 201);
    }

    public function sendVoice(Request $request): JsonResponse
    {
        $request->validate([
            'listing_id' => 'required|uuid',
            'receiver_id' => 'required|uuid',
            'voice' => 'required|file|mimes:webm,ogg,mp3,wav|max:10240',
            'target_lang' => 'nullable|string|max:5',
        ]);

        $voicePath = $request->file('voice')->store('voice-messages', 'public');

        $message = $this->messageService->sendVoice([
            'listing_id' => $request->listing_id,
            'sender_id' => $request->user()->profile->id,
            'receiver_id' => $request->receiver_id,
            'voice_path' => storage_path('app/public/' . $voicePath),
            'voice_url' => asset('storage/' . $voicePath),
            'target_lang' => $request->target_lang ?? 'en',
        ]);

        return response()->json(['data' => $message], 201);
    }

    public function conversation(Request $request, string $channel): JsonResponse
    {
        $messages = $this->messageService->getConversation(
            $channel,
            $request->integer('limit', 50),
            $request->get('before')
        );

        return response()->json(['data' => $messages]);
    }

    public function markRead(Request $request, string $channel): JsonResponse
    {
        $count = $this->messageService->markAsRead($channel, $request->user()->profile->id);

        return response()->json(['marked_read' => $count]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = $this->messageService->getUnreadCount($request->user()->profile->id);

        return response()->json(['unread_count' => $count]);
    }
}
