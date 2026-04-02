'use client';

import { Shield, CheckCircle, Star } from 'lucide-react';

interface TrustBadgeProps {
  verified: boolean;
  rating?: number;
  reviewCount?: number;
}

export function TrustBadge({ verified, rating, reviewCount }: TrustBadgeProps) {
  return (
    <div className="flex items-center gap-2 flex-wrap">
      {verified && (
        <span className="inline-flex items-center gap-1 bg-green-50 text-green-700 text-xs font-medium px-2 py-1 rounded-full">
          <Shield className="w-3 h-3" />
          Verified
        </span>
      )}
      {rating !== undefined && rating > 0 && (
        <span className="inline-flex items-center gap-1 bg-yellow-50 text-yellow-700 text-xs font-medium px-2 py-1 rounded-full">
          <Star className="w-3 h-3 fill-current" />
          {rating.toFixed(1)}
          {reviewCount !== undefined && <span className="text-yellow-500">({reviewCount})</span>}
        </span>
      )}
      {rating !== undefined && rating >= 4.5 && (
        <span className="inline-flex items-center gap-1 bg-blue-50 text-blue-700 text-xs font-medium px-2 py-1 rounded-full">
          <CheckCircle className="w-3 h-3" />
          Top Rated
        </span>
      )}
    </div>
  );
}
