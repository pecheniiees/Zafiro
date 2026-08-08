<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-8">
            <h2 class="text-2xl font-bold text-center text-gray-900 dark:text-white mb-6">
                Вход в аккаунт
            </h2>

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded">
                    <ul class="text-red-600 dark:text-red-200 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('auth.login') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Номер телефона
                    </label>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        value="{{ old('phone') }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white outline-none"
                        placeholder="+7 (999) 123-45-67"
                    >
                    @error('phone')
                        <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Пароль
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white outline-none"
                        placeholder="••••••••"
                    >
                </div>

                <div class="flex items-center">
                    <input
                        type="checkbox"
                        id="remember"
                        name="remember"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-700"
                    >
                    <label for="remember" class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                        Запомнить меня
                    </label>
                </div>

                <button
                    type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-lg transition"
                >
                    Войти
                </button>
            </form>

            <p class="text-center text-sm text-gray-600 dark:text-gray-400 mt-6">
                Нет аккаунта?
                <a href="{{ route('auth.show-register') }}" class="text-blue-600 hover:text-blue-700 font-medium">
                    Зарегистрироваться
                </a>
            </p>
        </div>
    </div>
</body>
</html>
