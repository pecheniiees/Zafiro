<?php

namespace App\Http\Resources;

use App\Models\Dashboard;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $dashboard = $request->route('dashboard');
        $settings = $dashboard instanceof Dashboard
            ? SettingsService::load($dashboard)
            : SettingsService::defaults();

        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'category' => $this->category,
            'price' => (float) $this->price,
            'currency' => $settings['finances']['currency'] ?? 'KZT',
            'quantity' => $this->quantity,
            'in_stock' => $this->quantity > 0,
            'image_url' => $request->root().'/images/product-placeholder.svg',
        ];
    }
}
