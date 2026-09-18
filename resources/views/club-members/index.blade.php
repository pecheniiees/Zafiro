@extends('layouts.admin')

@section('page-title', 'Пользователи')

@section('content')
@php
    $activeMembers = $members->where('status', 'active')->count();
    $blockedMembers = $members->where('status', 'blocked')->count();
    $totalBalance = $members->sum(fn ($member) => (float) $member->balance);
    $totalBonusBalance = $members->sum(fn ($member) => (float) $member->bonus_balance);
@endphp

<div class="space-y-5 p-5 lg:p-8">
    @if (session('status'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
            <ul class="list-disc space-y-1 ps-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="animate-slide rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-purple-600 dark:text-purple-400">Клубная база</p>
                <h3 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">Пользователи клуба</h3>
                <p class="mt-2 max-w-2xl text-sm text-gray-600 dark:text-gray-400">Управляйте клиентами, балансами и статусами внутри текущего клуба.</p>
            </div>
            <button type="button" id="open-create-member" class="inline-flex items-center justify-center gap-2 rounded-lg bg-purple-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-600 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                <i class="fas fa-user-plus"></i>
                <span>Создать пользователя</span>
            </button>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950/40">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Всего</span>
                    <i class="fas fa-users text-gray-400"></i>
                </div>
                <p class="mt-3 text-2xl font-bold text-gray-900 dark:text-white">{{ $members->count() }}</p>
            </div>
            <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-900/60 dark:bg-green-900/10">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-sm font-medium text-green-700 dark:text-green-300">Активные</span>
                    <i class="fas fa-circle-check text-green-500"></i>
                </div>
                <p class="mt-3 text-2xl font-bold text-green-700 dark:text-green-300">{{ $activeMembers }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950/40">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Баланс</span>
                    <i class="fas fa-wallet text-gray-400"></i>
                </div>
                <p class="mt-3 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalBalance, 0, ',', ' ') }} KZT</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950/40">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Бонусы</span>
                    <i class="fas fa-star text-amber-500"></i>
                </div>
                <p class="mt-3 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalBonusBalance, 0, ',', ' ') }}</p>
            </div>
        </div>
    </section>

    <section class="animate-slide overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-6 py-4 dark:border-gray-800">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Список пользователей</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Заблокированных: {{ $blockedMembers }}</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-950/40 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold">Пользователь</th>
                        <th class="px-4 py-3 text-left font-semibold">Телефон</th>
                        <th class="px-4 py-3 text-right font-semibold">Баланс</th>
                        <th class="px-4 py-3 text-right font-semibold">Бонусы</th>
                        <th class="px-4 py-3 text-left font-semibold">Статус</th>
                        <th class="px-6 py-3 text-left font-semibold">Добавлен</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($members as $member)
                        <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-900 text-sm font-bold text-white dark:bg-gray-100 dark:text-gray-900">
                                        {{ mb_substr($member->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $member->user->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $member->user->email ?: 'Email не указан' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 font-medium text-gray-700 dark:text-gray-300">{{ $member->user->phone }}</td>
                            <td class="px-4 py-4 text-right font-semibold text-gray-900 dark:text-gray-100">{{ number_format((float) $member->balance, 0, ',', ' ') }} KZT</td>
                            <td class="px-4 py-4 text-right font-semibold text-amber-600 dark:text-amber-400">{{ number_format((float) $member->bonus_balance, 0, ',', ' ') }}</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $member->status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : ($member->status === 'blocked' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300') }}">
                                    {{ $statusNames[$member->status] ?? $member->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $member->created_at->format('d.m.Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <p class="mt-4 font-semibold text-gray-900 dark:text-gray-100">Пользователей пока нет</p>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Создайте первого клиента клуба через кнопку сверху.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<div id="create-member-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-950/85 p-4 backdrop-blur-md" aria-hidden="true">
    <div class="w-full max-w-2xl max-h-[88vh] overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900">
        <div class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-gray-200 bg-white px-5 py-4 dark:border-gray-800 dark:bg-gray-900">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-purple-600 dark:text-purple-400">Новый клиент</p>
                <h3 class="mt-1 text-lg font-bold text-gray-900 dark:text-white">Создать пользователя</h3>
            </div>
            <button type="button" data-close-create-member class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100" aria-label="Закрыть">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form action="{{ route('club-members.store') }}" method="POST" class="space-y-5 p-5">
            @csrf
            <div>
                <h4 class="mb-2 text-sm font-bold text-gray-900 dark:text-white">Данные пользователя</h4>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Имя</label>
                        <input id="name" name="name" value="{{ old('name') }}" required maxlength="255" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                    <div>
                        <label for="phone" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Телефон</label>
                        <input id="phone" name="phone" value="{{ old('phone') }}" required placeholder="77001234567" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                    <div class="md:col-span-2">
                        <label for="email" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" maxlength="255" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                </div>
            </div>

            <div>
                <h4 class="mb-2 text-sm font-bold text-gray-900 dark:text-white">Клубный счет</h4>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="status" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Статус</label>
                        <select id="status" name="status" required class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                            <option value="active" @selected(old('status', 'active') === 'active')>Активен</option>
                            <option value="inactive" @selected(old('status') === 'inactive')>Неактивен</option>
                            <option value="blocked" @selected(old('status') === 'blocked')>Заблокирован</option>
                        </select>
                    </div>
                    <div>
                        <label for="balance" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Баланс</label>
                        <input id="balance" type="number" step="0.01" min="0" name="balance" value="{{ old('balance', 0) }}" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                    <div>
                        <label for="bonus_balance" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Бонусный баланс</label>
                        <input id="bonus_balance" type="number" step="0.01" min="0" name="bonus_balance" value="{{ old('bonus_balance', 0) }}" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                </div>
            </div>

            <div>
                <h4 class="mb-2 text-sm font-bold text-gray-900 dark:text-white">Доступ</h4>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Пароль</label>
                        <input id="password" type="password" name="password" required class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                    <div>
                        <label for="password_confirmation" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Повтор пароля</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-gray-200 pt-4 dark:border-gray-800">
                <button type="button" data-close-create-member class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Отмена</button>
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-purple-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-purple-700">
                    <i class="fas fa-check"></i>
                    <span>Создать пользователя</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const modal = document.getElementById('create-member-modal');
    const openButton = document.getElementById('open-create-member');

    if (! modal || ! openButton) {
        return;
    }

    const open = () => {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        modal.querySelector('input, select, button')?.focus();
    };

    const close = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        openButton.focus();
    };

    openButton.addEventListener('click', open);
    modal.querySelectorAll('[data-close-create-member]').forEach((button) => button.addEventListener('click', close));
    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            close();
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && ! modal.classList.contains('hidden')) {
            close();
        }
    });

    @if ($errors->any())
        open();
    @endif
})();
</script>
@endpush
