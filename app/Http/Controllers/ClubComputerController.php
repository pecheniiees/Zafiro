<?php

namespace App\Http\Controllers;

use App\Actions\CreateDashboardForUserAction;
use App\Http\Requests\ControlClubComputersRequest;
use App\Http\Requests\UpdateClubComputerLayoutRequest;
use App\Http\Requests\UpdateClubComputerRequest;
use App\Models\ClubComputer;
use App\Models\Dashboard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClubComputerController extends Controller
{
    public function __construct(private CreateDashboardForUserAction $createDashboard) {}

    public function control(ControlClubComputersRequest $request): JsonResponse
    {
        $data = $request->validated();
        $status = match ($data['action']) {
            'turn_on' => ClubComputer::STATUS_ON,
            'turn_off' => ClubComputer::STATUS_OFF,
            'reserve' => ClubComputer::STATUS_RESERVED,
            'maintenance' => ClubComputer::STATUS_MAINTENANCE,
        };
        $dashboard = $this->dashboard($request);

        $updated = ClubComputer::query()
            ->whereKey($data['computer_ids'])
            ->whereHas('zone', fn ($query) => $query->where('dashboard_id', $dashboard->id))
            ->update(['status' => $status]);

        return response()->json(['updated' => $updated, 'status' => $status]);
    }

    public function update(UpdateClubComputerRequest $request, ClubComputer $clubComputer): RedirectResponse
    {
        $this->authorizeComputer($this->dashboard($request), $clubComputer);

        $clubComputer->update($request->validated());

        return back()->with('status', 'Данные ПК обновлены.');
    }

    public function __invoke(UpdateClubComputerLayoutRequest $request, ClubComputer $clubComputer): JsonResponse
    {
        $this->authorizeComputer($this->dashboard($request), $clubComputer);

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

    private function dashboard(Request $request): Dashboard
    {
        return $request->user()->dashboard ?? $this->createDashboard->handle($request->user());
    }

    private function authorizeComputer(Dashboard $dashboard, ClubComputer $clubComputer): void
    {
        $clubComputer->loadMissing('zone');

        abort_unless((int) $clubComputer->zone?->dashboard_id === $dashboard->id, 404);
    }
}
