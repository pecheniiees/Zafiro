@extends('settings.layout')

@section('settings-content')
<h4 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Настройка клуба</h4>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Время завершения сессии при бездействии</label>
        <input type="text" name="session_idle_timeout" value="{{ $settings['club']['session_idle_timeout'] ?? '15 минут' }}" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-4 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-600">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Время перезагрузки ПК после завершения сессии</label>
        <input type="text" name="pc_restart_after_session_end" value="{{ $settings['club']['pc_restart_after_session_end'] ?? '30 секунд' }}" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-4 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-600">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Время до автоматического завершения сессии при недоступности ПК</label>
        <input type="text" name="session_auto_terminate_when_pc_unavailable" value="{{ $settings['club']['session_auto_terminate_when_pc_unavailable'] ?? '5 минут' }}" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-4 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-600">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Интервал между бронированиями</label>
        <input type="text" name="booking_interval" value="{{ $settings['club']['booking_interval'] ?? '30 минут' }}" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-4 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-600">
    </div>
</div>
@endsection
