<?php

namespace App\Http\Controllers;

use App\Actions\CreateDashboardForUserAction;
use App\Models\Dashboard;
use App\Services\SettingsService;
use App\Services\TariffApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(private CreateDashboardForUserAction $createDashboard) {}

    public function club(Request $request): View
    {
        return view('settings.club', ['settings' => SettingsService::load($this->dashboard($request))]);
    }

    public function finances(Request $request): View
    {
        return view('settings.finances', ['settings' => SettingsService::load($this->dashboard($request))]);
    }

    public function tariffs(Request $request): View
    {
        $dashboard = $this->dashboard($request);

        return view('settings.tariffs', [
            'settings' => SettingsService::load($dashboard),
            'zones' => $dashboard->clubZones()->whereHas('computers')->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function guests(Request $request): View
    {
        return view('settings.guests', ['settings' => SettingsService::load($this->dashboard($request))]);
    }

    public function api(Request $request, TariffApiService $tariffs): View
    {
        $dashboard = $this->dashboard($request);
        $settings = SettingsService::load($dashboard);
        $endpoints = [];

        $hiddenSettingEndpoints = [
            'tariffs.standard_hour_price',
            'tariffs.vip_hour_price',
            'tariffs.night_package_price',
            'tariffs.minimum_session_minutes',
            'tariffs.zones',
        ];

        foreach ($settings as $section => $sectionSettings) {
            foreach ($sectionSettings as $key => $value) {
                if (in_array($section.'.'.$key, $hiddenSettingEndpoints, true)) {
                    continue;
                }

                $endpoints[] = [
                    'section' => $section,
                    'key' => $key,
                    'name' => SettingsService::labels()[$section][$key],
                    'value' => $value,
                    'url' => route('api.settings.show', [$dashboard, $section, $key]),
                ];
            }
        }

        $apiRoutes = collect(Route::getRoutes())->filter(fn ($route): bool => str_starts_with($route->uri(), 'api/'))
            ->map(function ($route) use ($dashboard, $settings): array {
                $parameters = [
                    'dashboard' => $dashboard,
                    'section' => array_key_first($settings),
                    'key' => array_key_first($settings[array_key_first($settings)] ?? []),
                ];

                return [
                    'methods' => array_values(array_diff($route->methods(), ['HEAD'])),
                    'uri' => $route->uri(),
                    'name' => $route->getName(),
                    'middleware' => $route->gatherMiddleware(),
                    'url' => $route->getName() ? route($route->getName(), array_intersect_key($parameters, array_flip($route->parameterNames()))) : url($route->uri()),
                    'action' => $route->getActionName(),
                ];
            })
            ->values()
            ->all();

        return view('settings.api', [
            'settings' => $settings,
            'endpoints' => $endpoints,
            'apiRoutes' => $apiRoutes,
            'ip' => request()->getHost(),
            'shellKey' => $dashboard->shell_key,
            'shellResolveEndpoint' => [
                'name' => 'Подключение клиентского shell',
                'url' => route('api.shell.resolve'),
                'request' => ['shell_key' => $dashboard->shell_key],
                'response' => [
                    'dashboard' => [
                        'id' => $dashboard->id,
                        'name' => $dashboard->name,
                        'slug' => $dashboard->slug,
                        'status' => $dashboard->status,
                        'plan' => $dashboard->plan,
                    ],
                    'api' => [
                        'products_url' => route('api.products.index', $dashboard),
                        'tariffs_url' => route('api.tariffs.index', $dashboard),
                    'auth_login_url' => route('api.auth.login'),
                    ],
                ],
            ],
            'authEndpoint' => [
                'name' => 'Авторизация клиента клуба',
                'url' => route('api.auth.login'),
                'request' => [
                    'shell_key' => $dashboard->shell_key,
                    'login' => '77001234567',
                    'password' => 'secret123',
                ],
                'response' => [
                    'dashboard' => [
                        'id' => $dashboard->id,
                        'name' => $dashboard->name,
                        'slug' => $dashboard->slug,
                    ],
                    'member' => [
                        'id' => 1,
                        'status' => 'active',
                        'balance' => 1500,
                        'bonus_balance' => 200,
                        'user' => [
                            'id' => 2,
                            'name' => 'Client Name',
                            'phone' => '77001234567',
                            'email' => 'client@example.com',
                        ],
                    ],
                ],
            ],
            'productEndpoint' => [
                'name' => 'Товары для мобильного приложения',
                'url' => route('api.products.index', $dashboard),
                'response' => [
                    'data' => [[
                        'id' => 1,
                        'sku' => 'DRK-001',
                        'name' => 'Coca-Cola 0,5 л',
                        'category' => 'Напитки',
                        'price' => 650,
                        'currency' => $settings['finances']['currency'] ?? 'KZT',
                        'quantity' => 48,
                        'in_stock' => true,
                        'image_url' => request()->root().'/images/product-placeholder.svg',
                    ]],
                ],
            ],
            'tariffEndpoint' => [
                'name' => 'Тарифы по зонам',
                'url' => route('api.tariffs.index', $dashboard),
                'response' => $tariffs->payload($dashboard),
            ],
        ]);
    }

    public function showSetting(Request $request, Dashboard $dashboard, string $section, string $key): JsonResponse
    {
        $settings = SettingsService::load($dashboard);

        abort_unless(array_key_exists($section, $settings), 404);
        abort_unless(array_key_exists($key, $settings[$section]), 404);

        return response()->json([
            'ip' => $request->getHost(),
            'dashboard' => $dashboard->slug,
            'setting' => $section.'.'.$key,
            'name' => SettingsService::labels()[$section][$key],
            'value' => $settings[$section][$key],
        ]);
    }

    public function save(Request $request): RedirectResponse
    {
        $section = $request->input('section', 'club');
        $dashboard = $this->dashboard($request);
        $settings = SettingsService::load($dashboard);
        $settings[$section] = $settings[$section] ?? [];

        $fields = [
            'club' => [
                'session_idle_timeout',
                'pc_restart_after_session_end',
                'session_auto_terminate_when_pc_unavailable',
                'booking_interval',
            ],
            'finances' => [
                'currency',
            ],
            'tariffs' => [
                'standard_hour_price',
                'vip_hour_price',
                'night_package_price',
                'minimum_session_minutes',
            ],
            'guests' => [
                'show_qr_code_on_login',
                'allow_guest_self_transfer',
                'allow_adding_friends',
                'allow_balance_transfer_to_friends',
            ],
        ];

        $booleanFields = [
            'show_qr_code_on_login',
            'allow_guest_self_transfer',
            'allow_adding_friends',
            'allow_balance_transfer_to_friends',
        ];

        foreach ($fields[$section] ?? [] as $field) {
            if ($request->has($field)) {
                $settings[$section][$field] = in_array($field, $booleanFields, true)
                    ? $request->boolean($field)
                    : $request->input($field);
            }
        }

        if ($section === 'tariffs') {
            $allowedZoneIds = $dashboard->clubZones()->whereHas('computers')->pluck('id')->map(fn (int $id): string => (string) $id)->all();
            $zoneTariffs = [];

            $requestedZoneTariffs = $request->input('zones', []);

            foreach (is_array($requestedZoneTariffs) ? $requestedZoneTariffs : [] as $zoneId => $tariffs) {
                if (! in_array((string) $zoneId, $allowedZoneIds, true) || ! is_array($tariffs)) {
                    continue;
                }

                $items = [];
                $requestedTariffs = $tariffs['tariffs'] ?? [];

                foreach (is_array($requestedTariffs) ? $requestedTariffs : [] as $tariff) {
                    if (! is_array($tariff) || trim((string) ($tariff['name'] ?? '')) === '') {
                        continue;
                    }

                    $items[] = [
                        'name' => trim((string) $tariff['name']),
                        'price' => (string) max(0, (float) ($tariff['price'] ?? 0)),
                        'duration_minutes' => (string) max(1, (int) ($tariff['duration_minutes'] ?? 60)),
                    ];
                }

                $zoneTariffs[(string) $zoneId] = $items;
            }

            $settings['tariffs']['zones'] = $zoneTariffs;
        }

        SettingsService::save($dashboard, $settings);

        return back()->with('status', 'Настройки сохранены');
    }

    private function dashboard(Request $request): Dashboard
    {
        return $request->user()->dashboard ?? $this->createDashboard->handle($request->user());
    }
}
