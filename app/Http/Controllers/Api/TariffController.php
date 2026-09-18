<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dashboard;
use App\Services\TariffApiService;
use Illuminate\Http\JsonResponse;

class TariffController extends Controller
{
    public function __invoke(Dashboard $dashboard, TariffApiService $tariffs): JsonResponse
    {
        return response()
            ->json($tariffs->payload($dashboard))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }
}
