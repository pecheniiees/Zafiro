<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dashboard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShellController extends Controller
{
    public function resolve(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shell_key' => ['required', 'string', 'max:64'],
        ]);

        $dashboard = Dashboard::query()
            ->select(['id', 'name', 'slug', 'status', 'plan'])
            ->where('shell_key', $validated['shell_key'])
            ->first();

        abort_unless($dashboard, 404);

        return response()->json([
            'dashboard' => [
                'id' => $dashboard->id,
                'name' => $dashboard->name,
                'slug' => $dashboard->slug,
                'status' => $dashboard->status,
                'plan' => $dashboard->plan,
            ],
            'api' => [
                'products_url' => route('api.products.index', $dashboard),
                'tariffs_url' => route('api.tariffs.index', $dashboard),
            ],
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }
}
