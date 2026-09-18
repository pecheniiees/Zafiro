<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClubMember;
use App\Models\Dashboard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shell_key' => ['required', 'string', 'max:64'],
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $dashboard = Dashboard::query()
            ->select(['id', 'name', 'slug', 'status', 'plan', 'shell_key'])
            ->where('shell_key', $validated['shell_key'])
            ->first();

        if (! $dashboard) {
            throw ValidationException::withMessages([
                'shell_key' => ['Клуб с таким ключом не найден.'],
            ]);
        }

        $member = $dashboard->clubMembers()
            ->with('user:id,name,phone,email,password')
            ->whereHas('user', function ($query) use ($validated): void {
                $query->where('phone', $validated['login'])
                    ->orWhere('email', $validated['login']);
            })
            ->first();

        if (! $member || ! Hash::check($validated['password'], $member->user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Неверный логин или пароль.'],
            ]);
        }

        if ($member->status !== ClubMember::STATUS_ACTIVE) {
            throw ValidationException::withMessages([
                'login' => ['Пользователь не активен.'],
            ]);
        }

        return response()->json([
            'dashboard' => [
                'id' => $dashboard->id,
                'name' => $dashboard->name,
                'slug' => $dashboard->slug,
                'status' => $dashboard->status,
                'plan' => $dashboard->plan,
            ],
            'member' => [
                'id' => $member->id,
                'status' => $member->status,
                'balance' => (float) $member->balance,
                'bonus_balance' => (float) $member->bonus_balance,
                'user' => [
                    'id' => $member->user->id,
                    'name' => $member->user->name,
                    'phone' => $member->user->phone,
                    'email' => $member->user->email,
                ],
            ],
            'api' => [
                'products_url' => route('api.products.index', $dashboard),
                'tariffs_url' => route('api.tariffs.index', $dashboard),
            ],
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }
}
