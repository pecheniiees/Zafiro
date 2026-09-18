<?php

namespace App\Services;

use App\Models\Dashboard;

class TariffApiService
{
    public function payload(Dashboard $dashboard): array
    {
        $settings = SettingsService::load($dashboard);
        $zoneTariffs = $settings['tariffs']['zones'] ?? [];
        $currency = $settings['finances']['currency'] ?? 'KZT';

        $zones = $dashboard->clubZones()
            ->withCount('computers')
            ->whereHas('computers')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return [
            'dashboard' => $dashboard->slug,
            'currency' => $currency,
            'data' => $zones->map(fn ($zone): array => [
                'zone' => [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'description' => $zone->description,
                    'icon' => $zone->icon,
                    'computers_count' => $zone->computers_count,
                ],
                'tariffs' => collect($zoneTariffs[(string) $zone->id] ?? [])->map(fn (array $tariff): array => [
                    'name' => $tariff['name'] ?? 'Тариф',
                    'price' => (float) ($tariff['price'] ?? 0),
                    'currency' => $currency,
                    'duration_minutes' => (int) ($tariff['duration_minutes'] ?? 60),
                ])->values()->all(),
            ])->values()->all(),
        ];
    }
}
