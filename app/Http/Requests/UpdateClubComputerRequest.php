<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClubComputerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:100'],
            'inventory_number' => ['nullable', 'string', 'max:80'],
            'ip_address' => ['nullable', 'ip'],
            'specs' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['off', 'on', 'reserved', 'maintenance'])],
        ];
    }
}
