<?php

return [
    'driver' => env('SCOUT_DRIVER', 'meilisearch'),

    'prefix' => env('SCOUT_PREFIX', 'pearlhub_'),

    'queue' => env('SCOUT_QUEUE', true),

    'after_commit' => true,

    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],

    'soft_delete' => false,

    'identify' => env('SCOUT_IDENTIFY', false),

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key' => env('MEILISEARCH_KEY'),
        'index-settings' => [
            \App\Models\StaysListing::class => [
                'filterableAttributes' => ['city', 'status', 'property_type', 'provider_id'],
                'sortableAttributes' => ['price_per_night', 'rating', 'created_at'],
                'searchableAttributes' => ['title', 'description', 'city', 'address'],
            ],
            \App\Models\VehiclesListing::class => [
                'filterableAttributes' => ['city', 'status', 'vehicle_type', 'brand', 'provider_id'],
                'sortableAttributes' => ['price_per_day', 'rating', 'created_at'],
                'searchableAttributes' => ['title', 'description', 'brand', 'model', 'city'],
            ],
            \App\Models\EventsListing::class => [
                'filterableAttributes' => ['city', 'status', 'event_type', 'provider_id'],
                'sortableAttributes' => ['price', 'rating', 'event_date', 'created_at'],
                'searchableAttributes' => ['title', 'description', 'city', 'venue_name'],
            ],
            \App\Models\PropertiesListing::class => [
                'filterableAttributes' => ['city', 'status', 'property_type', 'listing_type', 'provider_id'],
                'sortableAttributes' => ['price', 'rating', 'area_sqft', 'created_at'],
                'searchableAttributes' => ['title', 'description', 'city', 'address'],
            ],
            \App\Models\SmeProduct::class => [
                'filterableAttributes' => ['business_id', 'category', 'stock_status'],
                'sortableAttributes' => ['price', 'created_at'],
                'searchableAttributes' => ['name', 'description', 'category', 'sku'],
            ],
        ],
    ],
];
