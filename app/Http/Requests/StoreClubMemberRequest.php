<?php

namespace App\Http\Requests;

use App\Models\ClubMember;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClubMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^\+?[1-9]\d{1,14}$/', 'unique:users,phone'],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'balance' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'bonus_balance' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'status' => ['required', Rule::in([
                ClubMember::STATUS_ACTIVE,
                ClubMember::STATUS_BLOCKED,
                ClubMember::STATUS_INACTIVE,
            ])],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'Имя обязательно.',
            'phone.required' => 'Телефон обязателен.',
            'phone.regex' => 'Введите корректный телефон.',
            'phone.unique' => 'Пользователь с таким телефоном уже существует.',
            'email.email' => 'Введите корректный email.',
            'password.required' => 'Пароль обязателен.',
            'password.min' => 'Пароль должен содержать минимум 6 символов.',
            'password.confirmed' => 'Пароли не совпадают.',
            'balance.min' => 'Баланс не может быть отрицательным.',
            'bonus_balance.min' => 'Бонусный баланс не может быть отрицательным.',
            'status.required' => 'Статус обязателен.',
            'status.in' => 'Выбран некорректный статус.',
        ];
    }
}
