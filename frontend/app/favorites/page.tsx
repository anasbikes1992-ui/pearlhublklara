'use client';

import { useEffect, useState } from 'react';
import { apiClient } from '@/lib/api';
import { Heart, Trash2 } from 'lucide-react';
import Link from 'next/link';

interface FavoriteItem {
  id: string;
  listing_id: string;
  listing_type: string;
  created_at: string;
}

const TYPE_LABELS: Record<string, string> = {
  stays: 'Stay',
  vehicles: 'Vehicle',
  events: 'Event',
  properties: 'Property',
  sme_products: 'SME Product',
};

export default function FavoritesPage() {
  const [favorites, setFavorites] = useState<FavoriteItem[]>([]);
  const [filter, setFilter] = useState<string>('');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadFavorites();
  }, [filter]);

  const loadFavorites = async () => {
    setLoading(true);
    try {
      const params = filter ? `?listing_type=${filter}` : '';
      const res = await apiClient.get(`/favorites${params}`);
      setFavorites(res.data.data || []);
    } catch {
      // handle error
    } finally {
      setLoading(false);
    }
  };

  const removeFavorite = async (item: FavoriteItem) => {
    await apiClient.post('/favorites/toggle', { listing_id: item.listing_id, listing_type: item.listing_type });
    setFavorites((prev) => prev.filter((f) => f.id !== item.id));
  };

  return (
    <div className="max-w-4xl mx-auto px-4 py-8">
      <div className="flex items-center justify-between mb-6">
        <h1 className="text-2xl font-bold flex items-center gap-2">
          <Heart className="w-6 h-6 text-red-500 fill-current" />
          My Favorites
        </h1>
        <select
          value={filter}
          onChange={(e) => setFilter(e.target.value)}
          className="border rounded-lg px-3 py-2 text-sm"
        >
          <option value="">All Types</option>
          {Object.entries(TYPE_LABELS).map(([key, label]) => (
            <option key={key} value={key}>{label}</option>
          ))}
        </select>
      </div>

      {loading ? (
        <div className="text-center py-12 text-gray-500">Loading...</div>
      ) : favorites.length === 0 ? (
        <div className="text-center py-12 text-gray-500">
          <Heart className="w-12 h-12 mx-auto mb-3 text-gray-300" />
          <p>No favorites yet. Browse listings and tap the heart to save them here.</p>
        </div>
      ) : (
        <div className="space-y-3">
          {favorites.map((item) => (
            <div key={item.id} className="flex items-center justify-between bg-white border rounded-xl px-4 py-3">
              <div>
                <Link href={`/${item.listing_type.replace('sme_products', 'sme')}/${item.listing_id}`} className="font-medium text-primary-500 hover:underline">
                  View Listing
                </Link>
                <span className="ml-2 text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">
                  {TYPE_LABELS[item.listing_type] || item.listing_type}
                </span>
              </div>
              <button onClick={() => removeFavorite(item)} className="p-2 text-gray-400 hover:text-red-500 transition-colors">
                <Trash2 className="w-4 h-4" />
              </button>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
