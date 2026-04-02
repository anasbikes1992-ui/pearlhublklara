'use client';

import { useSearchParams } from 'next/navigation';
import { ChatPanel } from '@/components/chat/chat-panel';
import { useAuthStore } from '@/lib/auth-store';
import { MessageCircle } from 'lucide-react';

export default function ChatPage() {
  const searchParams = useSearchParams();
  const { user } = useAuthStore();

  const channel = searchParams.get('channel');
  const listingId = searchParams.get('listing');
  const receiverId = searchParams.get('receiver');

  if (!user) {
    return (
      <div className="max-w-2xl mx-auto px-4 py-12 text-center">
        <MessageCircle className="w-12 h-12 mx-auto mb-3 text-gray-300" />
        <p className="text-gray-500">Please sign in to access chat.</p>
      </div>
    );
  }

  if (!channel || !listingId || !receiverId) {
    return (
      <div className="max-w-2xl mx-auto px-4 py-12 text-center">
        <MessageCircle className="w-12 h-12 mx-auto mb-3 text-gray-300" />
        <p className="text-gray-500">Select a listing and provider to start a conversation.</p>
      </div>
    );
  }

  return (
    <div className="max-w-2xl mx-auto px-4 py-6" style={{ height: 'calc(100vh - 5rem)' }}>
      <ChatPanel
        channel={channel}
        listingId={listingId}
        receiverId={receiverId}
        currentUserId={user.profile?.id || ''}
      />
    </div>
  );
}
