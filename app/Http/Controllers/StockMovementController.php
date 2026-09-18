<?php

namespace App\Http\Controllers;

use App\Actions\AdjustStockAction;
use App\Actions\CreateDashboardForUserAction;
use App\Http\Requests\StoreStockMovementRequest;
use App\Models\Dashboard;
use Illuminate\Http\RedirectResponse;

class StockMovementController extends Controller
{
    public function __construct(private CreateDashboardForUserAction $createDashboard) {}

    public function __invoke(
        StoreStockMovementRequest $request,
        AdjustStockAction $adjustStock,
    ): RedirectResponse
    {
        $adjustStock->handle($this->dashboard($request), $request->validated(), $request->user()?->getKey());

        return back()->with('status', 'Складская операция выполнена');
    }

    private function dashboard(StoreStockMovementRequest $request): Dashboard
    {
        return $request->user()->dashboard ?? $this->createDashboard->handle($request->user());
    }
}
