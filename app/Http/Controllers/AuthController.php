<?php

namespace App\Http\Controllers;

use App\Actions\CreateDashboardForUserAction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'phone' => ['required', 'string', 'regex:/^\+?[1-9]\d{1,14}$/'],
            'password' => ['required'],
        ], [
            'phone.required' => 'Номер телефона обязателен.',
            'phone.regex' => 'Введите корректный номер телефона.',
            'password.required' => 'Пароль обязателен.',
        ]);

        if (Auth::attempt(['phone' => $credentials['phone'], 'password' => $credentials['password']], $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'phone' => 'Неверные учетные данные.',
        ])->onlyInput('phone');
    }

    /**
     * Show the registration form
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle registration
     */
    public function register(Request $request, CreateDashboardForUserAction $createDashboard)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^\+?[1-9]\d{1,14}$/', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'Имя обязательно.',
            'phone.required' => 'Номер телефона обязателен.',
            'phone.regex' => 'Введите корректный номер телефона.',
            'phone.unique' => 'Этот номер телефона уже зарегистрирован.',
            'password.required' => 'Пароль обязателен.',
            'password.min' => 'Пароль должен содержать минимум 6 символов.',
            'password.confirmed' => 'Пароли не совпадают.',
        ]);

        $user = DB::transaction(function () use ($validated, $createDashboard): User {
            $user = User::create([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
            ]);

            $createDashboard->handle($user);

            return $user;
        });

        Auth::login($user);

        return redirect('/dashboard')->with('success', 'Добро пожаловать!');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
