<?php

namespace App\Http\Controllers;

use App\Http\Requests\ControlClubComputersRequest;
use App\Http\Requests\UpdateClubComputerLayoutRequest;
use App\Models\ClubComputer;
use Illuminate\Http\JsonResponse;

class ClubComputerController extends Controller
{
    public function control(ControlClubComputersRequest $request): JsonResponse
    {
        $data = $request->validated();
        $status = match ($data['action']) {
            'power_on' => 'online',
            'restart' => 'restarting',
            'power_off' => 'offline',
        };

        $updated = ClubComputer::query()->whereKey($data['computer_ids'])->update(['status' => $status]);

        return response()->json(['updated' => $updated, 'status' => $status]);
    }

    public function __invoke(UpdateClubComputerLayoutRequest $request, ClubComputer $clubComputer): JsonResponse
    {
        $layout = $request->validated();
        $occupied = ClubComputer::query()
            ->where('club_zone_id', $clubComputer->club_zone_id)
            ->whereKeyNot($clubComputer->getKey())
            ->where('position_x', $layout['position_x'])
            ->where('position_y', $layout['position_y'])
            ->exists();

        if ($occupied) {
            return response()->json(['message' => 'Эта ячейка уже занята другим компьютером.'], 409);
        }

        $clubComputer->update($layout);

        return response()->json(['saved' => true]);
    }
}
