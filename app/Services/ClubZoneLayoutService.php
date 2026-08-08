<?php

namespace App\Services;

use App\Models\ClubZone;
use Illuminate\Support\Facades\DB;

class ClubZoneLayoutService
{
    public function align(): void
    {
        DB::transaction(function (): void {
            $placed = [];

            ClubZone::query()->orderBy('position_y')->orderBy('position_x')->orderBy('id')->lockForUpdate()->get()
                ->each(function (ClubZone $zone) use (&$placed): void {
                    $width = max(180, (int) round($zone->width / 20) * 20);
                    $height = max(140, (int) round($zone->height / 20) * 20);
                    $originX = max(0, (int) round($zone->position_x / 20) * 20);
                    $originY = max(0, (int) round($zone->position_y / 20) * 20);
                    [$x, $y] = $this->nearestFreePosition($originX, $originY, $width, $height, $placed);

                    $zone->update(['position_x' => $x, 'position_y' => $y, 'width' => $width, 'height' => $height]);
                    $placed[] = compact('x', 'y', 'width', 'height');
                });
        });
    }

    /** @param array<int, array{x: int, y: int, width: int, height: int}> $placed */
    private function nearestFreePosition(int $originX, int $originY, int $width, int $height, array $placed): array
    {
        if ($this->isFree($originX, $originY, $width, $height, $placed)) {
            return [$originX, $originY];
        }

        for ($radius = 1; $radius <= 100; $radius++) {
            for ($offsetY = -$radius; $offsetY <= $radius; $offsetY++) {
                for ($offsetX = -$radius; $offsetX <= $radius; $offsetX++) {
                    if (max(abs($offsetX), abs($offsetY)) !== $radius) {
                        continue;
                    }
                    $x = $originX + ($offsetX * 20);
                    $y = $originY + ($offsetY * 20);
                    if ($x >= 0 && $y >= 0 && $this->isFree($x, $y, $width, $height, $placed)) {
                        return [$x, $y];
                    }
                }
            }
        }

        return [$originX, $originY];
    }

    /** @param array<int, array{x: int, y: int, width: int, height: int}> $placed */
    private function isFree(int $x, int $y, int $width, int $height, array $placed): bool
    {
        return collect($placed)->doesntContain(fn (array $zone): bool => $x < $zone['x'] + $zone['width'] + 20
            && $x + $width + 20 > $zone['x']
            && $y < $zone['y'] + $zone['height'] + 20
            && $y + $height + 20 > $zone['y']);
    }
}
