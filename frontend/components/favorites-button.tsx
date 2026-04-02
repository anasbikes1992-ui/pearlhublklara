'use client';

import { useState, useEffect } from 'react';
import { Heart } from 'lucide-react';
import { apiClient } from '@/lib/api';
import { useAuthStore } from '@/lib/auth-store';

interface FavoritesButtonProps {
  listingId: string;
  listingType: string;
  className?: string;
}

export function FavoritesButton({ listingId, listingType, className = '' }: FavoritesButtonProps) {
  const { user } = useAuthStore();
  const [favorited, setFavorited] = useState(false);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (!user) return;
    apiClient.post('/favorites/check', { listing_id: listingId, listing_type: listingType })
      .then((res) => setFavorited(res.data.favorited))
      .catch(() => {});
  }, [listingId, listingType, user]);

  const toggle = async () => {
    if (!user || loading) return;
    setLoading(true);
    try {
      const res = await apiClient.post('/favorites/toggle', { listing_id: listingId, listing_type: listingType });
      setFavorited(res.data.favorited);
    } catch {
      // ignore
    } finally {
      setLoading(false);
    }
  };

  if (!user) return null;

  return (
    <button
      onClick={toggle}
      disabled={loading}
      className={`p-2 rounded-full transition-colors ${favorited ? 'text-red-500' : 'text-gray-400 hover:text-red-400'} ${className}`}
    >
      <Heart className={`w-5 h-5 ${favorited ? 'fill-current' : ''}`} />
    </button>
  );
}
