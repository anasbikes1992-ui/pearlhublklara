<?php

namespace App\Services;

use App\Models\Message;
use App\Events\NewChatMessage;

class MessageService
{
    protected TranslationService $translation;

    public function __construct(TranslationService $translation)
    {
        $this->translation = $translation;
    }

    public function send(array $data): Message
    {
        $translated = null;
        if (!empty($data['target_lang']) && $data['target_lang'] !== ($data['original_lang'] ?? 'en')) {
            $translated = $this->translation->translateText(
                $data['message'],
                $data['original_lang'] ?? 'en',
                $data['target_lang']
            );
        }

        $message = Message::create([
            'listing_id' => $data['listing_id'],
            'channel' => $data['channel'] ?? $this->generateChannel($data),
            'sender_id' => $data['sender_id'],
            'receiver_id' => $data['receiver_id'],
            'message' => $data['message'],
            'is_voice' => $data['is_voice'] ?? false,
            'voice_url' => $data['voice_url'] ?? null,
            'original_text' => $data['is_voice'] ? $data['original_text'] ?? null : $data['message'],
            'translated_text' => $translated,
            'original_lang' => $data['original_lang'] ?? 'en',
            'target_lang' => $data['target_lang'] ?? 'en',
            'is_read' => false,
        ]);

        broadcast(new NewChatMessage($message))->toOthers();

        return $message;
    }

    public function sendVoice(array $data): Message
    {
        $voiceResult = $this->translation->translateVoiceMessage(
            $data['voice_path'],
            $data['target_lang'] ?? 'en'
        );

        $data['is_voice'] = true;
        $data['message'] = $voiceResult['original_text'] ?? '[Voice Message]';
        $data['original_text'] = $voiceResult['original_text'];
        $data['translated_text'] = $voiceResult['translated_text'];

        return $this->send($data);
    }

    public function getConversation(string $channel, int $limit = 50, ?string $before = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Message::where('channel', $channel)
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($before) {
            $query->where('created_at', '<', $before);
        }

        return $query->get()->reverse()->values();
    }

    public function markAsRead(string $channel, string $userId): int
    {
        return Message::where('channel', $channel)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    public function getUnreadCount(string $userId): int
    {
        return Message::where('receiver_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    protected function generateChannel(array $data): string
    {
        $ids = collect([$data['sender_id'], $data['receiver_id']])->sort()->implode('-');
        return "chat.{$data['listing_id']}.{$ids}";
    }
}
