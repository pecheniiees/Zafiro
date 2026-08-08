<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateClubZoneLayoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'position_x' => ['required', 'integer', 'min:0', 'max:5000'],
            'position_y' => ['required', 'integer', 'min:0', 'max:5000'],
            'width' => ['required', 'integer', 'min:180', 'max:1600'],
            'height' => ['required', 'integer', 'min:130', 'max:1200'],
        ];
    }
}
