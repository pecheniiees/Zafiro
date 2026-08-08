@extends('settings.layout')

@section('settings-section', 'guests')

@section('settings-content')
<style>
    .settings-switch {
        position: relative;
        width: 2.75rem;
        height: 1.5rem;
        flex-shrink: 0;
        cursor: pointer;
        appearance: none;
        border-radius: 9999px;
        background: #d1d5db;
        transition: background-color 150ms ease;
    }

    .settings-switch::after {
        position: absolute;
        top: 0.125rem;
        left: 0.125rem;
        width: 1.25rem;
        height: 1.25rem;
        content: '';
        border-radius: 9999px;
        background: #fff;
        transition: transform 150ms ease;
    }

    .settings-switch:checked {
        background: #9333ea;
    }

    .settings-switch:checked::after {
        transform: translateX(1.25rem);
    }

    .settings-switch:focus-visible {
        outline: 2px solid #9333ea;
        outline-offset: 2px;
    }
</style>

<h4 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Гости</h4>
<div class="flex flex-col items-start gap-3">
    <div class="inline-flex items-center gap-3 rounded-xl border border-gray-200 p-4 dark:border-gray-700">
        <label for="show_qr_code_on_login" class="text-sm font-medium text-gray-700 dark:text-gray-300">
            Отображать QR-код на странице авторизации
        </label>
        <input type="hidden" name="show_qr_code_on_login" value="0">
        <input
            id="show_qr_code_on_login"
            type="checkbox"
            role="switch"
            name="show_qr_code_on_login"
            value="1"
            class="settings-switch"
            @checked($settings['guests']['show_qr_code_on_login'] ?? false)
        >
    </div>

    <div class="inline-flex items-center gap-3 rounded-xl border border-gray-200 p-4 dark:border-gray-700">
        <label for="allow_guest_self_transfer" class="text-sm font-medium text-gray-700 dark:text-gray-300">
            Разрешить самостоятельную пересадку гостя
        </label>
        <input type="hidden" name="allow_guest_self_transfer" value="0">
        <input
            id="allow_guest_self_transfer"
            type="checkbox"
            role="switch"
            name="allow_guest_self_transfer"
            value="1"
            class="settings-switch"
            @checked($settings['guests']['allow_guest_self_transfer'] ?? false)
        >
    </div>

    <div class="inline-flex items-center gap-3 rounded-xl border border-gray-200 p-4 dark:border-gray-700">
        <label for="allow_adding_friends" class="text-sm font-medium text-gray-700 dark:text-gray-300">
            Возможность добавления в друзья
        </label>
        <input type="hidden" name="allow_adding_friends" value="0">
        <input
            id="allow_adding_friends"
            type="checkbox"
            role="switch"
            name="allow_adding_friends"
            value="1"
            class="settings-switch"
            @checked($settings['guests']['allow_adding_friends'] ?? false)
        >
    </div>

    <div class="inline-flex items-center gap-3 rounded-xl border border-gray-200 p-4 dark:border-gray-700">
        <label for="allow_balance_transfer_to_friends" class="text-sm font-medium text-gray-700 dark:text-gray-300">
            Разрешить перевод баланса друзьям
        </label>
        <input type="hidden" name="allow_balance_transfer_to_friends" value="0">
        <input
            id="allow_balance_transfer_to_friends"
            type="checkbox"
            role="switch"
            name="allow_balance_transfer_to_friends"
            value="1"
            class="settings-switch"
            @checked($settings['guests']['allow_balance_transfer_to_friends'] ?? false)
        >
    </div>
</div>
@endsection
