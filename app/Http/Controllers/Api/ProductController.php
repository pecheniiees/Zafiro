<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Dashboard;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function __invoke(Dashboard $dashboard): AnonymousResourceCollection
    {
        $products = $dashboard->products()
            ->select(['id', 'dashboard_id', 'sku', 'name', 'category', 'price', 'quantity'])
            ->orderBy('name')
            ->paginate(50);

        return ProductResource::collection($products);
    }
}
