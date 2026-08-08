@extends('layouts.admin')

@section('page-title', 'Админпанель')

@section('content')
<div class="p-6 lg:p-8">
                <!-- Welcome Section -->
                <div class="mb-8 animate-slide">
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                        Добро пожаловать, {{ Auth::user()->name }}! 👋
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Вот краткая информация о вашем бизнесе
                    </p>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Sales -->
                    <div class="stat-card bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-800 animate-slide" style="animation-delay: 0.1s">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Sales</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">$12,345</p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-to-br from-purple-100 to-purple-200 dark:from-purple-900 dark:to-purple-800 rounded-lg flex items-center justify-center">
                                <i class="fas fa-shopping-cart text-purple-600 dark:text-purple-400 text-xl"></i>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 text-green-600 text-sm">
                            <i class="fas fa-arrow-up"></i>
                            <span>20% от прошлого месяца</span>
                        </div>
                    </div>

                    <!-- Total Expense -->
                    <div class="stat-card bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-800 animate-slide" style="animation-delay: 0.2s">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Expense</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">$3,213</p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900 dark:to-blue-800 rounded-lg flex items-center justify-center">
                                <i class="fas fa-credit-card text-blue-600 dark:text-blue-400 text-xl"></i>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 text-red-600 text-sm">
                            <i class="fas fa-arrow-down"></i>
                            <span>8% от прошлого месяца</span>
                        </div>
                    </div>

                    <!-- Total Orders -->
                    <div class="stat-card bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-800 animate-slide" style="animation-delay: 0.3s">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Orders</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">342</p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-to-br from-green-100 to-green-200 dark:from-green-900 dark:to-green-800 rounded-lg flex items-center justify-center">
                                <i class="fas fa-boxes text-green-600 dark:text-green-400 text-xl"></i>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 text-green-600 text-sm">
                            <i class="fas fa-arrow-up"></i>
                            <span>15% от прошлого месяца</span>
                        </div>
                    </div>

                    <!-- Total Revenue -->
                    <div class="stat-card bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-800 animate-slide" style="animation-delay: 0.4s">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Net Revenue</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">$9,132</p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-to-br from-orange-100 to-orange-200 dark:from-orange-900 dark:to-orange-800 rounded-lg flex items-center justify-center">
                                <i class="fas fa-chart-line text-orange-600 dark:text-orange-400 text-xl"></i>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 text-green-600 text-sm">
                            <i class="fas fa-arrow-up"></i>
                            <span>12% от прошлого месяца</span>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Main Chart -->
                    <div class="lg:col-span-2 bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-800 animate-slide" style="animation-delay: 0.5s">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Sales & Purchases</h3>
                        <canvas id="chart" style="max-height: 300px;"></canvas>
                    </div>

                    <!-- Quick Stats -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-800 animate-slide" style="animation-delay: 0.6s">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Quick Stats</h3>
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Completion Rate</span>
                                    <span class="text-sm font-bold text-purple-600">87%</span>
                                </div>
                                <div class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full">
                                    <div class="h-full bg-gradient-to-r from-purple-500 to-purple-600 rounded-full" style="width: 87%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Customer Satisfaction</span>
                                    <span class="text-sm font-bold text-blue-600">92%</span>
                                </div>
                                <div class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full">
                                    <div class="h-full bg-gradient-to-r from-blue-500 to-blue-600 rounded-full" style="width: 92%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">On-Time Delivery</span>
                                    <span class="text-sm font-bold text-green-600">95%</span>
                                </div>
                                <div class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full">
                                    <div class="h-full bg-gradient-to-r from-green-500 to-green-600 rounded-full" style="width: 95%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Invoice -->
                <div class="bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-800 animate-slide" style="animation-delay: 0.7s">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Recent Invoice</h3>
                        <a href="#" class="text-purple-600 hover:text-purple-700 dark:text-purple-400 text-sm font-medium">View All →</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b border-gray-200 dark:border-gray-800">
                                <tr>
                                    <th class="text-left py-3 font-semibold text-gray-700 dark:text-gray-300">Invoice</th>
                                    <th class="text-left py-3 font-semibold text-gray-700 dark:text-gray-300">Date</th>
                                    <th class="text-left py-3 font-semibold text-gray-700 dark:text-gray-300">Amount</th>
                                    <th class="text-left py-3 font-semibold text-gray-700 dark:text-gray-300">Status</th>
                                    <th class="text-right py-3 font-semibold text-gray-700 dark:text-gray-300">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                                    <td class="py-4 text-gray-900 dark:text-gray-100 font-medium">#INV001</td>
                                    <td class="py-4 text-gray-600 dark:text-gray-400">2026-08-05</td>
                                    <td class="py-4 text-gray-900 dark:text-gray-100 font-semibold">$1,200</td>
                                    <td class="py-4">
                                        <span class="inline-flex items-center gap-2 px-3 py-1 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded-full text-xs font-semibold">
                                            <i class="fas fa-check-circle"></i> Paid
                                        </span>
                                    </td>
                                    <td class="py-4 text-right">
                                        <button class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                                            <i class="fas fa-ellipsis-h"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                                    <td class="py-4 text-gray-900 dark:text-gray-100 font-medium">#INV002</td>
                                    <td class="py-4 text-gray-600 dark:text-gray-400">2026-08-04</td>
                                    <td class="py-4 text-gray-900 dark:text-gray-100 font-semibold">$850</td>
                                    <td class="py-4">
                                        <span class="inline-flex items-center gap-2 px-3 py-1 bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400 rounded-full text-xs font-semibold">
                                            <i class="fas fa-clock"></i> Pending
                                        </span>
                                    </td>
                                    <td class="py-4 text-right">
                                        <button class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                                            <i class="fas fa-ellipsis-h"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                                    <td class="py-4 text-gray-900 dark:text-gray-100 font-medium">#INV003</td>
                                    <td class="py-4 text-gray-600 dark:text-gray-400">2026-08-03</td>
                                    <td class="py-4 text-gray-900 dark:text-gray-100 font-semibold">$2,100</td>
                                    <td class="py-4">
                                        <span class="inline-flex items-center gap-2 px-3 py-1 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded-full text-xs font-semibold">
                                            <i class="fas fa-check-circle"></i> Paid
                                        </span>
                                    </td>
                                    <td class="py-4 text-right">
                                        <button class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                                            <i class="fas fa-ellipsis-h"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
</div>
@endsection

@push('scripts')
<script>
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('overlay');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
    }

    const userMenuBtn = document.getElementById('userMenuBtn');
    const userMenu = document.getElementById('userMenu');

    if (userMenuBtn) {
        userMenuBtn.addEventListener('click', () => {
            userMenu.classList.toggle('active');
        });
    }

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.relative')) {
            userMenu?.classList.remove('active');
        }
    });

    const chartElement = document.getElementById('chart');
    if (chartElement) {
        const ctx = chartElement.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [
                    {
                        label: 'Sales Target',
                        data: [5000, 6000, 5500, 6500, 7000],
                        backgroundColor: 'rgba(168, 85, 247, 0.1)',
                        borderColor: 'rgba(168, 85, 247, 0.5)',
                        borderWidth: 1,
                        borderRadius: 8
                    },
                    {
                        label: 'Sales',
                        data: [4200, 5100, 4800, 5900, 6200],
                        backgroundColor: 'rgba(168, 85, 247, 0.8)',
                        borderColor: 'rgba(168, 85, 247, 1)',
                        borderWidth: 1,
                        borderRadius: 8
                    },
                    {
                        label: 'Purchases',
                        data: [3000, 3500, 3200, 3800, 4100],
                        backgroundColor: 'rgba(239, 68, 68, 0.8)',
                        borderColor: 'rgba(239, 68, 68, 1)',
                        borderWidth: 1,
                        borderRadius: 8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            color: '#6b7280',
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 7000,
                        ticks: {
                            stepSize: 1000,
                            color: '#9ca3af',
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            color: 'rgba(209, 213, 219, 0.1)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#9ca3af',
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
</script>
@endpush
