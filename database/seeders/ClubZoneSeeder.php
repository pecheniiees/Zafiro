<?php

namespace Database\Seeders;

use App\Models\ClubZone;
use App\Services\ClubZoneComputerService;
use Illuminate\Database\Seeder;

class ClubZoneSeeder extends Seeder
{
    public function run(ClubZoneComputerService $computers): void
    {
        $zones = [
            ['name' => 'VIP-зал', 'description' => 'Персональные игровые места', 'capacity' => 8, 'icon' => 'crown', 'theme' => 'green', 'status' => 'available', 'is_featured' => true, 'sort_order' => 10, 'position_x' => 20, 'position_y' => 20, 'width' => 390, 'height' => 380],
            ['name' => 'Основной зал', 'description' => 'Компьютерная зона', 'capacity' => 20, 'icon' => 'desktop', 'theme' => 'neutral', 'status' => 'available', 'is_featured' => false, 'sort_order' => 20, 'position_x' => 425, 'position_y' => 20, 'width' => 330, 'height' => 180],
            ['name' => 'Консоли', 'description' => 'PlayStation-зона', 'capacity' => 10, 'icon' => 'gamepad', 'theme' => 'neutral', 'status' => 'available', 'is_featured' => false, 'sort_order' => 30, 'position_x' => 770, 'position_y' => 20, 'width' => 300, 'height' => 180],
            ['name' => 'Бар', 'description' => 'Касса и напитки', 'capacity' => null, 'icon' => 'mug-hot', 'theme' => 'yellow', 'status' => 'service', 'is_featured' => false, 'sort_order' => 40, 'position_x' => 425, 'position_y' => 215, 'width' => 330, 'height' => 185],
            ['name' => 'Лаунж', 'description' => 'Ожидание и отдых', 'capacity' => null, 'icon' => 'couch', 'theme' => 'purple', 'status' => 'available', 'is_featured' => false, 'sort_order' => 50, 'position_x' => 770, 'position_y' => 215, 'width' => 300, 'height' => 185],
        ];

        foreach ($zones as $zone) {
            $clubZone = ClubZone::query()->updateOrCreate(['name' => $zone['name']], $zone);
            $computers->sync($clubZone);
        }
    }
}
