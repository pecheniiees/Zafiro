<?php

namespace App\Services;

use App\Models\Dashboard;

class SettingsService
{
    public static function load(Dashboard $dashboard): array
    {
        return array_replace_recursive(
            self::defaults(),
            $dashboard->settings ?? [],
        );
    }

    public static function save(Dashboard $dashboard, array $settings): void
    {
        $dashboard->forceFill([
            'settings' => array_replace_recursive(self::defaults(), $settings),
        ])->save();
    }

    public static function defaults(): array
    {
        return [
            'club' => [
                'session_idle_timeout' => '15 минут',
                'pc_restart_after_session_end' => '30 секунд',
                'session_auto_terminate_when_pc_unavailable' => '5 минут',
                'booking_interval' => '30 минут',
            ],
            'finances' => [
                'currency' => 'KZT',
            ],
            'tariffs' => [
                'standard_hour_price' => '700',
                'vip_hour_price' => '1000',
                'night_package_price' => '3500',
                'minimum_session_minutes' => '30',
            ],
            'guests' => [
                'show_qr_code_on_login' => false,
                'allow_guest_self_transfer' => false,
                'allow_adding_friends' => false,
                'allow_balance_transfer_to_friends' => false,
            ],
        ];
    }

    public static function labels(): array
    {
        return [
            'club' => [
                'session_idle_timeout' => 'Время завершения сессии при бездействии',
                'pc_restart_after_session_end' => 'Время перезагрузки ПК после завершения сессии',
                'session_auto_terminate_when_pc_unavailable' => 'Время до автоматического завершения сессии при недоступности ПК',
                'booking_interval' => 'Интервал между бронированиями',
            ],
            'finances' => [
                'currency' => 'Валюта',
            ],
            'tariffs' => [
                'standard_hour_price' => 'Обычный тариф за час',
                'vip_hour_price' => 'VIP тариф за час',
                'night_package_price' => 'Ночной пакет',
                'minimum_session_minutes' => 'Минимальная длительность сессии',
                'zones' => 'Тарифы по зонам',
            ],
            'guests' => [
                'show_qr_code_on_login' => 'Отображать QR-код на странице авторизации',
                'allow_guest_self_transfer' => 'Разрешить самостоятельную пересадку гостя',
                'allow_adding_friends' => 'Возможность добавления в друзья',
                'allow_balance_transfer_to_friends' => 'Разрешить перевод баланса друзьям',
            ],
        ];
    }
}
