<?php

namespace App\Http\Controllers;

use App\Actions\CreateDashboardForUserAction;
use App\Models\Dashboard;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function __construct(private CreateDashboardForUserAction $createDashboard) {}

    public function __invoke(Request $request): View
    {
        $dashboard = $this->dashboard($request);

        $products = $dashboard->products()
            ->select(['id', 'sku', 'name', 'category', 'price', 'quantity'])
            ->orderBy('name')
            ->get();

        $movements = $dashboard->stockMovements()
            ->select(['id', 'product_id', 'type', 'quantity', 'balance_after', 'note', 'created_at'])
            ->with('product:id,name')
            ->latest()
            ->limit(10)
            ->get();

        return view('warehouse', [
            'products' => $products,
            'movements' => $movements,
        ]);
    }

    private function dashboard(Request $request): Dashboard
    {
        return $request->user()->dashboard ?? $this->createDashboard->handle($request->user());
    }
}
