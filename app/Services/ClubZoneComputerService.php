<?php

namespace App\Services;

use App\Models\ClubComputer;
use App\Models\ClubZone;

class ClubZoneComputerService
{
    public function sync(ClubZone $zone): void
    {
        $capacity = max(0, (int) $zone->capacity);
        $zone->computers()->where('number', '>', $capacity)->delete();

        for ($number = 1; $number <= $capacity; $number++) {
            $index = $number - 1;
            $zone->computers()->firstOrCreate(
                ['number' => $number],
                [
                    'status' => ClubComputer::STATUS_OFF,
                    'position_x' => 5 + (($index % 6) * 47),
                    'position_y' => 5 + ((int) floor($index / 6) * 42),
                ],
            );
        }
    }
}
