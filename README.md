# PearlHub — Sri Lanka's Multi-Vertical Marketplace

A production-ready monorepo combining **Laravel 11 backend**, **Next.js 15 frontend**, and **Flutter mobile apps** for Sri Lanka's premier marketplace platform.

---

## Architecture

```
pearlhublklara/
├── backend/          ← Laravel 11 API (this root)
├── frontend/         ← Next.js 15 Web App
└── flutter/          ← Flutter Mobile Apps (Customer, Provider, Admin)
```

---

## Verticals

| Vertical | Description |
|----------|-------------|
| 🏨 Stays | Hotels, villas, apartments, guest houses |
| 🚗 Vehicles | Car, van, SUV, motorcycle, tuk-tuk rentals |
| 🎉 Events | Cultural, adventure, food & wellness experiences |
| 🏠 Properties | Real estate for sale and rent |
| 🏪 SME | Local restaurants, shops, services, crafts |
| 🚕 Taxi (PearlRide) | 13-category on-demand taxi with real-time tracking |
| 💬 Social | Community posts, sharing, local tips |

---

## Quick Start (Local)

### Prerequisites
- PHP 8.3+, Composer
- PostgreSQL 16
- Node.js 20+
- Docker (optional)

### With Docker

```bash
cp .env.example .env
docker-compose up -d
docker-compose exec backend php artisan key:generate
docker-compose exec backend php artisan migrate --seed
```

### Without Docker

```bash
# Backend
cp .env.example .env
# Edit .env with your DB credentials
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve  # http://localhost:8000

# Frontend
cd frontend
cp .env.example .env.local
npm install
npm run dev  # http://localhost:3000
```

---

## Laravel Backend

### Stack
- **Laravel 11** + PHP 8.3
- **PostgreSQL** with UUID primary keys
- **Laravel Sanctum** for API token auth
- **Role-based middleware** (8 user roles)

### Seeded Test Accounts
| Email | Password | Role |
|-------|----------|------|
| admin@pearlhub.lk | password | admin |
| customer@pearlhub.lk | password | customer |
| provider@pearlhub.lk | password | stays_provider |

### API Endpoints

```
POST   /api/auth/register
POST   /api/auth/login
GET    /api/auth/me

GET    /api/stays
GET    /api/vehicles
GET    /api/events
GET    /api/properties
GET    /api/sme
GET    /api/search?q=

POST   /api/bookings
GET    /api/wallet/balance
POST   /api/taxi/request
POST   /api/taxi/calculate-fare
POST   /api/ai/concierge

GET    /api/admin/overview       ← admin only
POST   /api/admin/moderate-listing
GET    /api/admin/kyc-review
```

### Roles
`customer` · `stays_provider` · `vehicle_provider` · `event_organizer` · `property_owner` · `taxi_driver` · `sme_owner` · `admin`

---

## Next.js Frontend

### Stack
- **Next.js 15** App Router
- **Tailwind CSS** with PearlHub theme
- **React Query** for server state
- **Zustand** for client state
- **Axios** API client

### Pages
- `/` — Home with featured listings
- `/auth` — Login / Register
- `/stays`, `/vehicles`, `/events`, `/properties`, `/sme` — Listing browsers
- `/taxi` — PearlRide booking with 13 vehicle categories
- `/social` — Community feed
- `/dashboard` — Provider booking management
- `/admin/*` — Admin panel (overview, moderation, users, KYC, settings)

---

## Flutter Apps

Three separate apps sharing a common Dart package:

| App | Description |
|-----|-------------|
| `flutter/customer` | Browse listings, book rides, AI Concierge |
| `flutter/provider` | Manage listings, accept bookings, driver dashboard |
| `flutter/admin` | Moderation, user management, platform settings |

### Shared Package (`flutter/shared`)
- **Supabase Realtime** for live taxi tracking
- **Freezed** models for all entities
- **Riverpod** state management
- **GoRouter** navigation
- Switchable **Google Maps / OpenStreetMap**

### Build Commands
```bash
cd flutter/customer
flutter pub get
flutter run --dart-define=SUPABASE_URL=xxx --dart-define=SUPABASE_ANON_KEY=xxx
```

---

## Taxi Service (PearlRide)

13 vehicle categories seeded out of the box:

| Category | Base Fare | Per Km |
|----------|-----------|--------|
| Tuk Tuk | LKR 100 | LKR 50 |
| Mini | LKR 200 | LKR 60 |
| Sedan | LKR 300 | LKR 70 |
| Premium | LKR 500 | LKR 100 |
| SUV | LKR 450 | LKR 90 |
| Van | LKR 500 | LKR 85 |
| Mini Bus | LKR 800 | LKR 120 |
| Luxury | LKR 1,000 | LKR 150 |
| Motorcycle | LKR 80 | LKR 40 |
| Women Only | LKR 300 | LKR 70 |
| Eco | LKR 250 | LKR 55 |
| Parcel | LKR 150 | LKR 45 |
| Airport Transfer | LKR 800 | LKR 80 |

---

## Environment Variables

See `.env.example` for the full list. Key variables:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_DATABASE=pearlhub

ANTHROPIC_API_KEY=        # AI Concierge
PAYHERE_MERCHANT_ID=      # Payment gateway
SUPABASE_URL=             # For Flutter apps
SUPABASE_ANON_KEY=
```

---

## Production Deployment

```bash
# Backend
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force

# Frontend
npm run build
npm start
```

---

## License
Proprietary — PearlHub © 2025
