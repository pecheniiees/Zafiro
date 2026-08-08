<aside class="sidebar h-screen w-64 shrink-0 sticky top-0 bg-gradient-to-b from-gray-900 to-gray-800 text-gray-100 flex flex-col shadow-lg">
    <div class="p-6 border-b border-gray-700">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-purple-400 to-blue-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-chart-line text-white text-lg"></i>
            </div>
            <h1 class="text-2xl font-bold bg-gradient-to-r from-purple-400 to-blue-400 bg-clip-text text-transparent">Panze</h1>
        </div>
    </div>

    <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-purple-600 to-purple-700 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700/50' }} rounded-lg font-medium transition">
            <i class="fas fa-chart-pie w-5 text-center"></i>
            <span>Админпанель</span>
        </a>
        <a href="{{ route('warehouse') }}" class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('warehouse') ? 'bg-gradient-to-r from-purple-600 to-purple-700 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700/50' }} rounded-lg font-medium transition">
            <i class="fas fa-warehouse w-5 text-center"></i>
            <span>Склад</span>
        </a>
        <a href="{{ route('club-map.index') }}" class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('club-map.*') ? 'bg-gradient-to-r from-purple-600 to-purple-700 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700/50' }} rounded-lg font-medium transition">
            <i class="fas fa-map-location-dot w-5 text-center"></i>
            <span>Карта зала</span>
        </a>
        <a href="{{ route('cash-register.index') }}" class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('cash-register.*') ? 'bg-gradient-to-r from-purple-600 to-purple-700 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700/50' }} rounded-lg font-medium transition">
            <i class="fas fa-cash-register w-5 text-center"></i>
            <span>Касса</span>
        </a>
        <a href="{{ route('settings') }}" class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('settings') ? 'bg-gradient-to-r from-purple-600 to-purple-700 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700/50' }} rounded-lg font-medium transition">
            <i class="fas fa-cog w-5 text-center"></i>
            <span>Настройки</span>
        </a>
    </nav>

    <div class="p-4 border-t border-gray-700 space-y-2">
        <form action="{{ route('auth.logout') }}" method="POST">
            @csrf
            <button
                type="submit"
                class="w-full flex items-center gap-3 px-4 py-2 bg-red-600/20 hover:bg-red-600/30 text-red-400 hover:text-red-300 rounded-lg transition font-medium text-sm"
            >
                <i class="fas fa-sign-out-alt w-4 text-center"></i>
                <span>Выйти</span>
            </button>
        </form>
    </div>
</aside>
