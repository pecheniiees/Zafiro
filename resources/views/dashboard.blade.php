@extends('layouts.admin')

@section('page-title', 'Админпанель')

@section('content')
<div class="space-y-6 p-6 lg:p-8">
    <section class="animate-slide rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-purple-600 dark:text-purple-400">{{ $dashboard->name }}</p>
                <h3 class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">Добро пожаловать, {{ Auth::user()->name }}!</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Живая сводка по текущему клубу: касса, клиенты, склад и карта зала.</p>
            </div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-950/40">
                    <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Slug</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $dashboard->slug }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-950/40">
                    <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Тариф</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ ucfirst($dashboard->plan) }}</p>
                </div>
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 dark:border-green-900/60 dark:bg-green-900/10">
                    <p class="text-xs font-medium uppercase text-green-700 dark:text-green-300">Статус</p>
                    <p class="mt-1 text-sm font-semibold text-green-700 dark:text-green-300">{{ ucfirst($dashboard->status) }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
        <div class="stat-card animate-slide rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Выручка сегодня</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['today_income'], 0, ',', ' ') }} KZT</p>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-300">
                    <i class="fas fa-cash-register"></i>
                </div>
            </div>
        </div>

        <div class="stat-card animate-slide rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Чистый месяц</p>
                    <p class="mt-2 text-2xl font-bold {{ $stats['month_net'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ number_format($stats['month_net'], 0, ',', ' ') }} KZT</p>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-300">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>

        <div class="stat-card animate-slide rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Пользователи клуба</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['club_members'] }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Активные: {{ $stats['active_members'] }}</p>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>

        <div class="stat-card animate-slide rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">ПК в зале</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['computers'] }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Включены: {{ $stats['computers_on'] }}</p>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-300">
                    <i class="fas fa-desktop"></i>
                </div>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="animate-slide rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 xl:col-span-2">
            <div class="mb-5 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Касса за 7 дней</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Доходы и расходы текущего клуба.</p>
                </div>
            </div>
            <canvas id="dashboardCashChart" class="max-h-80"></canvas>
        </div>

        <div class="animate-slide rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Оперативно</h3>
            <div class="mt-5 space-y-4">
                <div>
                    <div class="mb-2 flex items-center justify-between text-sm">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Зоны</span>
                        <span class="font-bold text-purple-600 dark:text-purple-400">{{ $stats['zones'] }}</span>
                    </div>
                    <div class="h-2 rounded-full bg-gray-200 dark:bg-gray-800"><div class="h-2 rounded-full bg-purple-600" style="width: {{ min(100, $stats['zones'] * 10) }}%"></div></div>
                </div>
                <div>
                    <div class="mb-2 flex items-center justify-between text-sm">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Бронь ПК</span>
                        <span class="font-bold text-blue-600 dark:text-blue-400">{{ $stats['computers_reserved'] }}</span>
                    </div>
                    <div class="h-2 rounded-full bg-gray-200 dark:bg-gray-800"><div class="h-2 rounded-full bg-blue-600" style="width: {{ $stats['computers'] ? min(100, round($stats['computers_reserved'] / $stats['computers'] * 100)) : 0 }}%"></div></div>
                </div>
                <div>
                    <div class="mb-2 flex items-center justify-between text-sm">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Тех обслуживание</span>
                        <span class="font-bold text-amber-600 dark:text-amber-400">{{ $stats['computers_maintenance'] }}</span>
                    </div>
                    <div class="h-2 rounded-full bg-gray-200 dark:bg-gray-800"><div class="h-2 rounded-full bg-amber-500" style="width: {{ $stats['computers'] ? min(100, round($stats['computers_maintenance'] / $stats['computers'] * 100)) : 0 }}%"></div></div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950/40">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Баланс клиентов</p>
                    <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['member_balance'], 0, ',', ' ') }} KZT</p>
                    <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">Бонусы: {{ number_format($stats['member_bonus_balance'], 0, ',', ' ') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="animate-slide rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 xl:col-span-2">
            <div class="mb-5 flex items-center justify-between gap-3">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Последние операции кассы</h3>
                <a href="{{ route('cash-register.index') }}" class="text-sm font-semibold text-purple-600 hover:text-purple-700 dark:text-purple-400">К кассе</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($recentTransactions as $transaction)
                            <tr>
                                <td class="py-3 font-medium text-gray-900 dark:text-gray-100">{{ $transaction->description ?: 'Операция кассы' }}</td>
                                <td class="py-3 text-gray-500 dark:text-gray-400">{{ $transaction->created_at->format('d.m H:i') }}</td>
                                <td class="py-3 text-right font-semibold {{ $transaction->type === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ $transaction->type === 'income' ? '+' : '-' }}{{ number_format((float) $transaction->amount, 0, ',', ' ') }} KZT</td>
                            </tr>
                        @empty
                            <tr><td class="py-8 text-center text-gray-500 dark:text-gray-400" colspan="3">Операций кассы пока нет.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="animate-slide rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-5 flex items-center justify-between gap-3">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Низкие остатки</h3>
                <a href="{{ route('warehouse') }}" class="text-sm font-semibold text-purple-600 hover:text-purple-700 dark:text-purple-400">Склад</a>
            </div>
            <div class="space-y-3">
                @forelse ($lowStockProducts as $product)
                    <div class="flex items-center justify-between gap-3 rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-950/40">
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $product->name }}</span>
                        <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700 dark:bg-red-900/30 dark:text-red-300">{{ $product->quantity }}</span>
                    </div>
                @empty
                    <p class="rounded-lg bg-gray-50 px-3 py-6 text-center text-sm text-gray-500 dark:bg-gray-950/40 dark:text-gray-400">Критичных остатков нет.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="animate-slide rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <h3 class="mb-5 text-lg font-bold text-gray-900 dark:text-white">Последние движения склада</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($recentMovements as $movement)
                        <tr>
                            <td class="py-3 font-medium text-gray-900 dark:text-gray-100">{{ $movement->product->name }}</td>
                            <td class="py-3 font-semibold {{ $movement->type === 'receipt' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ $movement->type === 'receipt' ? 'Оприходование' : 'Списание' }}</td>
                            <td class="py-3 text-right text-gray-700 dark:text-gray-300">{{ $movement->quantity }} шт.</td>
                            <td class="py-3 text-right text-gray-500 dark:text-gray-400">{{ $movement->created_at->format('d.m H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td class="py-8 text-center text-gray-500 dark:text-gray-400" colspan="4">Движений склада пока нет.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    const chartElement = document.getElementById('dashboardCashChart');
    if (chartElement) {
        new Chart(chartElement.getContext('2d'), {
            type: 'bar',
            data: {
                labels: @json($chart['labels']),
                datasets: [
                    {label: 'Доход', data: @json($chart['income']), backgroundColor: 'rgba(34, 197, 94, 0.8)', borderRadius: 6},
                    {label: 'Расход', data: @json($chart['expense']), backgroundColor: 'rgba(239, 68, 68, 0.75)', borderRadius: 6},
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {legend: {position: 'bottom'}},
                scales: {y: {beginAtZero: true}, x: {grid: {display: false}}},
            },
        });
    }
</script>
@endpush
