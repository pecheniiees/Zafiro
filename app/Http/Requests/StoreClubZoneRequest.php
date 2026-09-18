<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClubZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('icon') !== 'desktop') {
            $this->merge(['hourly_price' => null]);
        }
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:180'],
            'icon' => ['required', Rule::in(['crown', 'desktop', 'gamepad', 'mug-hot', 'couch', 'dice', 'door-open', 'bong', 'smoking'])],
            'theme' => ['required', Rule::in(['green', 'neutral', 'yellow', 'purple', 'blue'])],
            'status' => ['required', Rule::in(['available', 'busy', 'service'])],
            'hourly_price' => ['nullable', 'required_if:icon,desktop', 'numeric', 'min:0', 'max:9999999999.99'],
            'is_featured' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:10000'],
        ];
    }
}
