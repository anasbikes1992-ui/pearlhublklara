'use client';

import Link from 'next/link';
import { useAuthStore } from '@/lib/auth-store';
import { useLanguageStore } from '@/lib/language-store';
import { Menu, X, User, LogOut, Globe, Heart } from 'lucide-react';
import { useState } from 'react';

const LANGUAGES = [
  { code: 'en', label: 'English' },
  { code: 'si', label: 'Sinhala' },
  { code: 'ta', label: 'Tamil' },
  { code: 'hi', label: 'Hindi' },
  { code: 'ar', label: 'Arabic' },
  { code: 'zh', label: 'Chinese' },
  { code: 'fr', label: 'French' },
  { code: 'de', label: 'German' },
  { code: 'es', label: 'Spanish' },
  { code: 'ja', label: 'Japanese' },
];

export function Navbar() {
  const { user, logout, isAdmin, isProvider } = useAuthStore();
  const { language, setLanguage } = useLanguageStore();
  const [open, setOpen] = useState(false);
  const [langOpen, setLangOpen] = useState(false);

  return (
    <nav className="bg-white border-b border-gray-200 sticky top-0 z-50">
      <div className="max-w-6xl mx-auto px-4 flex items-center justify-between h-16">
        <Link href="/" className="text-xl font-bold text-primary-500">
          PearlHub
        </Link>

        {/* Desktop Nav */}
        <div className="hidden md:flex items-center gap-6 text-sm">
          <Link href="/stays" className="hover:text-primary-500 transition-colors">Stays</Link>
          <Link href="/vehicles" className="hover:text-primary-500 transition-colors">Vehicles</Link>
          <Link href="/events" className="hover:text-primary-500 transition-colors">Events</Link>
          <Link href="/properties" className="hover:text-primary-500 transition-colors">Properties</Link>
          <Link href="/sme" className="hover:text-primary-500 transition-colors">SME</Link>
          <Link href="/taxi" className="hover:text-primary-500 transition-colors">Taxi</Link>
        </div>

        <div className="hidden md:flex items-center gap-3">
          {/* Language Selector */}
          <div className="relative">
            <button
              onClick={() => setLangOpen(!langOpen)}
              className="flex items-center gap-1 px-2 py-1 text-sm rounded-lg hover:bg-gray-100 transition-colors"
            >
              <Globe className="w-4 h-4" />
              <span className="uppercase">{language}</span>
            </button>
            {langOpen && (
              <div className="absolute right-0 mt-2 w-40 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-50">
                {LANGUAGES.map((lang) => (
                  <button
                    key={lang.code}
                    onClick={() => { setLanguage(lang.code); setLangOpen(false); }}
                    className={`w-full text-left px-3 py-2 text-sm hover:bg-gray-50 ${language === lang.code ? 'text-primary-500 font-medium' : ''}`}
                  >
                    {lang.label}
                  </button>
                ))}
              </div>
            )}
          </div>

          {user ? (
            <>
              <Link href="/favorites" className="p-2 hover:bg-gray-100 rounded-lg">
                <Heart className="w-4 h-4" />
              </Link>
              {isProvider() && (
                <Link href="/dashboard" className="text-sm hover:text-primary-500">Dashboard</Link>
              )}
              {isAdmin() && (
                <Link href="/admin" className="text-sm text-coral-400 hover:text-coral-500">Admin</Link>
              )}
              <div className="flex items-center gap-2 text-sm">
                <User className="w-4 h-4" />
                <span>{user.profile?.full_name || user.name}</span>
              </div>
              <button onClick={logout} className="p-2 hover:bg-gray-100 rounded-lg">
                <LogOut className="w-4 h-4" />
              </button>
            </>
          ) : (
            <Link
              href="/auth"
              className="bg-primary-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-primary-600 transition-colors"
            >
              Sign In
            </Link>
          )}
        </div>

        {/* Mobile toggle */}
        <button className="md:hidden p-2" onClick={() => setOpen(!open)}>
          {open ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
        </button>
      </div>

      {/* Mobile Menu */}
      {open && (
        <div className="md:hidden border-t bg-white px-4 py-4 space-y-3">
          <Link href="/stays" className="block py-2" onClick={() => setOpen(false)}>Stays</Link>
          <Link href="/vehicles" className="block py-2" onClick={() => setOpen(false)}>Vehicles</Link>
          <Link href="/events" className="block py-2" onClick={() => setOpen(false)}>Events</Link>
          <Link href="/properties" className="block py-2" onClick={() => setOpen(false)}>Properties</Link>
          <Link href="/sme" className="block py-2" onClick={() => setOpen(false)}>SME</Link>
          <Link href="/taxi" className="block py-2" onClick={() => setOpen(false)}>Taxi</Link>
          <Link href="/social" className="block py-2" onClick={() => setOpen(false)}>Social</Link>
          {user && <Link href="/favorites" className="block py-2" onClick={() => setOpen(false)}>Favorites</Link>}
          <div className="py-2">
            <label className="text-xs text-gray-500 block mb-1">Language</label>
            <select
              value={language}
              onChange={(e) => setLanguage(e.target.value)}
              className="w-full border rounded-lg px-2 py-1 text-sm"
            >
              {LANGUAGES.map((lang) => (
                <option key={lang.code} value={lang.code}>{lang.label}</option>
              ))}
            </select>
          </div>
          {user ? (
            <>
              {isAdmin() && <Link href="/admin" className="block py-2 text-coral-400" onClick={() => setOpen(false)}>Admin Panel</Link>}
              <button onClick={() => { logout(); setOpen(false); }} className="block py-2 text-red-500">Sign Out</button>
            </>
          ) : (
            <Link href="/auth" className="block py-2 text-primary-500 font-medium" onClick={() => setOpen(false)}>Sign In</Link>
          )}
        </div>
      )}
    </nav>
  );
}
