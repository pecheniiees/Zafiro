<?php

namespace App\Http\Controllers;

use App\Actions\CreateDashboardForUserAction;
use App\Http\Requests\StoreClubZoneRequest;
use App\Http\Requests\UpdateClubZoneLayoutRequest;
use App\Http\Requests\UpdateClubZoneRequest;
use App\Models\ClubComputer;
use App\Models\ClubZone;
use App\Models\Dashboard;
use App\Services\ClubZoneLayoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClubZoneController extends Controller
{
    public function __construct(
        private ClubZoneLayoutService $layout,
        private CreateDashboardForUserAction $createDashboard,
    ) {}

    public function index(Request $request): View
    {
        $dashboard = $this->dashboard($request);

        return view('club-map.index', [
            'zones' => $dashboard->clubZones()->with('computers')->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function store(StoreClubZoneRequest $request): RedirectResponse
    {
        $dashboard = $this->dashboard($request);
        $offset = $dashboard->clubZones()->count() * 30;
        $dashboard->clubZones()->create($request->validated() + [
            'position_x' => 20 + ($offset % 240),
            'position_y' => 20 + ($offset % 180),
        ]);

        return back()->with('status', 'Зона добавлена. Добавьте ПК вручную в режиме редактора.');
    }

    public function update(UpdateClubZoneRequest $request, ClubZone $clubMap): RedirectResponse
    {
        $this->authorizeZone($this->dashboard($request), $clubMap);

        $clubMap->update($request->validated());

        return back()->with('status', 'Зона обновлена.');
    }

    public function destroy(Request $request, ClubZone $clubMap): RedirectResponse
    {
        $this->authorizeZone($this->dashboard($request), $clubMap);

        $clubMap->delete();

        return back()->with('status', 'Зона удалена.');
    }

    public function storeComputer(Request $request, ClubZone $clubMap): RedirectResponse
    {
        $this->authorizeZone($this->dashboard($request), $clubMap);

        $index = $clubMap->computers()->count();
        $number = ((int) $clubMap->computers()->max('number')) + 1;

        $clubMap->computers()->create([
            'number' => $number,
            'status' => ClubComputer::STATUS_OFF,
            'position_x' => 5 + (($index % 6) * 47),
            'position_y' => 5 + ((int) floor($index / 6) * 42),
        ]);

        return back()->with('status', 'ПК добавлен в зону.');
    }

    public function updateLayout(UpdateClubZoneLayoutRequest $request, ClubZone $clubMap): JsonResponse
    {
        $dashboard = $this->dashboard($request);
        $this->authorizeZone($dashboard, $clubMap);

        $layout = $request->validated();
        $gap = 20;
        $overlaps = $dashboard->clubZones()
            ->whereKeyNot($clubMap->getKey())
            ->get(['position_x', 'position_y', 'width', 'height'])
            ->contains(fn (ClubZone $zone): bool => $layout['position_x'] < $zone->position_x + $zone->width + $gap
                && $layout['position_x'] + $layout['width'] + $gap > $zone->position_x
                && $layout['position_y'] < $zone->position_y + $zone->height + $gap
                && $layout['position_y'] + $layout['height'] + $gap > $zone->position_y);

        if ($overlaps) {
            return response()->json(['message' => 'Карточки залов не могут пересекаться.'], 409);
        }

        $clubMap->update($layout);

        return response()->json(['saved' => true]);
    }

    public function align(Request $request): RedirectResponse
    {
        $this->layout->align($this->dashboard($request)->clubZones());

        return back()->with('status', 'Карточки выровнены по сетке с одинаковыми отступами.');
    }

    private function dashboard(Request $request): Dashboard
    {
        return $request->user()->dashboard ?? $this->createDashboard->handle($request->user());
    }

    private function authorizeZone(Dashboard $dashboard, ClubZone $clubZone): void
    {
        abort_unless((int) $clubZone->dashboard_id === $dashboard->id, 404);
    }
}
