<?php

namespace App\Http\Resources;

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
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'category' => $this->category,
            'price' => (float) $this->price,
            'currency' => 'KZT',
            'quantity' => $this->quantity,
            'in_stock' => $this->quantity > 0,
            'image_url' => $request->root().'/images/product-placeholder.svg',
        ];
    }
}
