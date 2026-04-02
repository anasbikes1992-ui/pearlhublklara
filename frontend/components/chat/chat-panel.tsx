'use client';

import { useState, useRef, useEffect } from 'react';
import { Send, Mic, MicOff, Loader2 } from 'lucide-react';
import { apiClient } from '@/lib/api';
import { useLanguageStore } from '@/lib/language-store';

interface ChatMessage {
  id: string;
  sender_id: string;
  message: string;
  translated_text?: string;
  is_voice: boolean;
  voice_url?: string;
  created_at: string;
}

interface ChatPanelProps {
  channel: string;
  listingId: string;
  receiverId: string;
  currentUserId: string;
}

export function ChatPanel({ channel, listingId, receiverId, currentUserId }: ChatPanelProps) {
  const [messages, setMessages] = useState<ChatMessage[]>([]);
  const [input, setInput] = useState('');
  const [loading, setLoading] = useState(false);
  const [recording, setRecording] = useState(false);
  const { language } = useLanguageStore();
  const messagesEndRef = useRef<HTMLDivElement>(null);
  const mediaRecorderRef = useRef<MediaRecorder | null>(null);
  const chunksRef = useRef<Blob[]>([]);

  useEffect(() => {
    loadMessages();
  }, [channel]);

  useEffect(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages]);

  const loadMessages = async () => {
    try {
      const res = await apiClient.get(`/chat/${channel}`);
      setMessages(res.data.data || []);
    } catch {
      // Channel may not exist yet
    }
  };

  const handleSend = async () => {
    if (!input.trim() || loading) return;
    setLoading(true);

    try {
      const res = await apiClient.post('/chat/send', {
        listing_id: listingId,
        receiver_id: receiverId,
        message: input.trim(),
        original_lang: language,
        target_lang: language === 'en' ? 'si' : 'en',
      });
      setMessages((prev) => [...prev, res.data.data]);
      setInput('');
    } catch (err) {
      console.error('Send failed:', err);
    } finally {
      setLoading(false);
    }
  };

  const startRecording = async () => {
    try {
      const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
      const recorder = new MediaRecorder(stream);
      chunksRef.current = [];

      recorder.ondataavailable = (e) => chunksRef.current.push(e.data);
      recorder.onstop = async () => {
        const blob = new Blob(chunksRef.current, { type: 'audio/webm' });
        stream.getTracks().forEach((t) => t.stop());
        await sendVoice(blob);
      };

      recorder.start();
      mediaRecorderRef.current = recorder;
      setRecording(true);
    } catch (err) {
      console.error('Microphone access denied:', err);
    }
  };

  const stopRecording = () => {
    mediaRecorderRef.current?.stop();
    setRecording(false);
  };

  const sendVoice = async (blob: Blob) => {
    setLoading(true);
    try {
      const formData = new FormData();
      formData.append('voice', blob, 'recording.webm');
      formData.append('listing_id', listingId);
      formData.append('receiver_id', receiverId);
      formData.append('target_lang', language === 'en' ? 'si' : 'en');

      const res = await apiClient.post('/chat/send-voice', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      setMessages((prev) => [...prev, res.data.data]);
    } catch (err) {
      console.error('Voice send failed:', err);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="flex flex-col h-full bg-white rounded-xl border border-gray-200">
      {/* Messages */}
      <div className="flex-1 overflow-y-auto p-4 space-y-3">
        {messages.map((msg) => {
          const isMine = msg.sender_id === currentUserId;
          return (
            <div key={msg.id} className={`flex ${isMine ? 'justify-end' : 'justify-start'}`}>
              <div className={`max-w-[75%] ${isMine ? 'bg-primary-500 text-white rounded-br-sm' : 'bg-gray-100 text-gray-800 rounded-bl-sm'} px-3 py-2 rounded-xl`}>
                {msg.is_voice && msg.voice_url && (
                  <audio controls className="mb-1 max-w-full" src={msg.voice_url} />
                )}
                <p className="text-sm">{msg.message}</p>
                {msg.translated_text && (
                  <p className={`text-xs mt-1 ${isMine ? 'text-white/70' : 'text-gray-500'} italic`}>
                    {msg.translated_text}
                  </p>
                )}
                <span className={`text-xs ${isMine ? 'text-white/60' : 'text-gray-400'} block mt-1`}>
                  {new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                </span>
              </div>
            </div>
          );
        })}
        <div ref={messagesEndRef} />
      </div>

      {/* Input */}
      <div className="border-t p-3">
        <div className="flex items-center gap-2">
          <button
            onClick={recording ? stopRecording : startRecording}
            className={`p-2 rounded-lg transition-colors ${recording ? 'bg-red-500 text-white animate-pulse' : 'hover:bg-gray-100 text-gray-500'}`}
          >
            {recording ? <MicOff className="w-5 h-5" /> : <Mic className="w-5 h-5" />}
          </button>
          <input
            type="text"
            value={input}
            onChange={(e) => setInput(e.target.value)}
            onKeyDown={(e) => e.key === 'Enter' && handleSend()}
            placeholder="Type a message..."
            className="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
            disabled={recording}
          />
          <button
            onClick={handleSend}
            disabled={loading || !input.trim()}
            className="bg-primary-500 text-white p-2 rounded-lg hover:bg-primary-600 disabled:opacity-50"
          >
            {loading ? <Loader2 className="w-5 h-5 animate-spin" /> : <Send className="w-5 h-5" />}
          </button>
        </div>
      </div>
    </div>
  );
}
