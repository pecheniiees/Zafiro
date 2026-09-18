<?php

use App\Actions\CreateDashboardForUserAction;
use App\Models\ClubMember;
use App\Models\Dashboard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createUserWithDashboard(array $userAttributes = []): array
{
    $user = User::factory()->create($userAttributes);
    $dashboard = app(CreateDashboardForUserAction::class)->handle($user);

    return [$user, $dashboard];
}

test('club settings can be saved to the current dashboard and reused on the next request', function () {
    [$user, $dashboard] = createUserWithDashboard();
    $this->actingAs($user);

    $response = $this->post('/settings/save', [
        'section' => 'club',
        'session_idle_timeout' => '20 минут',
        'pc_restart_after_session_end' => '45 секунд',
        'session_auto_terminate_when_pc_unavailable' => '10 минут',
        'booking_interval' => '40 минут',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('status', 'Настройки сохранены');

    $settings = $dashboard->refresh()->settings;

    expect($settings['club']['session_idle_timeout'])->toBe('20 минут');
    expect($settings['club']['pc_restart_after_session_end'])->toBe('45 секунд');
    expect($settings['club']['session_auto_terminate_when_pc_unavailable'])->toBe('10 минут');
    expect($settings['club']['booking_interval'])->toBe('40 минут');

    $this->get('/settings/club')
        ->assertSuccessful()
        ->assertViewHas('settings', fn (array $settings): bool => $settings['club']['session_idle_timeout'] === '20 минут');
});

test('settings are isolated between dashboards', function () {
    [$firstUser, $firstDashboard] = createUserWithDashboard(['name' => 'First Owner']);
    [$secondUser, $secondDashboard] = createUserWithDashboard(['name' => 'Second Owner']);

    $this->actingAs($firstUser)
        ->post('/settings/save', [
            'section' => 'finances',
            'currency' => 'RUB',
        ])
        ->assertRedirect();

    $this->actingAs($secondUser)
        ->get('/settings/finances')
        ->assertSuccessful()
        ->assertViewHas('settings', fn (array $settings): bool => $settings['finances']['currency'] === 'KZT');

    expect($firstDashboard->refresh()->settings['finances']['currency'])->toBe('RUB')
        ->and($secondDashboard->refresh()->settings)->toBeNull();
});

test('finance currency setting can be selected per dashboard', function () {
    [$user, $dashboard] = createUserWithDashboard();
    $this->actingAs($user);

    foreach (['KZT', 'RUB', '$'] as $currency) {
        $this->post('/settings/save', [
            'section' => 'finances',
            'currency' => $currency,
        ])->assertRedirect();

        expect($dashboard->refresh()->settings['finances']['currency'])->toBe($currency);
    }
});





test('tariff settings can be saved per dashboard zone tabs', function () {
    [$user, $dashboard] = createUserWithDashboard();
    $this->actingAs($user);
    $standardZone = $dashboard->clubZones()->create([
        'name' => 'Стандарт',
        'description' => 'Основной зал',
        'icon' => 'desktop',
        'theme' => 'blue',
        'status' => 'available',
        'is_featured' => false,
        'sort_order' => 10,
        'position_x' => 20,
        'position_y' => 20,
        'width' => 320,
        'height' => 180,
    ]);
    $standardZone->computers()->create(['number' => 1]);

    $vipZone = $dashboard->clubZones()->create([
        'name' => 'Премиум зона',
        'description' => 'VIP зал',
        'icon' => 'crown',
        'theme' => 'purple',
        'status' => 'available',
        'is_featured' => false,
        'sort_order' => 20,
        'position_x' => 380,
        'position_y' => 20,
        'width' => 320,
        'height' => 180,
    ]);

    $emptyZone = $dashboard->clubZones()->create([
        'name' => 'Пустая зона',
        'description' => 'Без ПК',
        'icon' => 'desktop',
        'theme' => 'neutral',
        'status' => 'available',
        'is_featured' => false,
        'sort_order' => 30,
        'position_x' => 740,
        'position_y' => 20,
        'width' => 320,
        'height' => 180,
    ]);

    $otherUser = User::factory()->create();
    $otherDashboard = app(CreateDashboardForUserAction::class)->handle($otherUser);
    $otherZone = $otherDashboard->clubZones()->create([
        'name' => 'Чужая зона',
        'icon' => 'desktop',
        'theme' => 'blue',
        'status' => 'available',
        'is_featured' => false,
        'sort_order' => 10,
        'position_x' => 20,
        'position_y' => 20,
        'width' => 320,
        'height' => 180,
    ]);

    $this->get('/settings/tariffs')
        ->assertSuccessful()
        ->assertSee('Стандарт')
        ->assertDontSee('Премиум зона')
        ->assertDontSee('Пустая зона')
        ->assertDontSee('Чужая зона');

    $this->post('/settings/save', [
        'section' => 'tariffs',
        'zones' => [
            $standardZone->id => [
                'tariffs' => [
                    ['name' => '1 час', 'price' => '800', 'duration_minutes' => '60'],
                    ['name' => 'Ночь', 'price' => '4200', 'duration_minutes' => '480'],
                ],
            ],
            $vipZone->id => [
                'tariffs' => [
                    ['name' => 'Не должен сохраниться', 'price' => '1500', 'duration_minutes' => '60'],
                ],
            ],
            $emptyZone->id => [
                'tariffs' => [
                    ['name' => 'Пустая зона тариф', 'price' => '7777', 'duration_minutes' => '777'],
                ],
            ],
            $otherZone->id => [
                'tariffs' => [
                    ['name' => 'Чужой тариф', 'price' => '9999', 'duration_minutes' => '999'],
                ],
            ],
        ],
    ])->assertRedirect()->assertSessionHas('status', 'Настройки сохранены');

    $zoneTariffs = $dashboard->refresh()->settings['tariffs']['zones'];

    expect($zoneTariffs[(string) $standardZone->id][0]['name'])->toBe('1 час')
        ->and($zoneTariffs[(string) $standardZone->id][0]['price'])->toBe('800')
        ->and($zoneTariffs[(string) $standardZone->id][1]['duration_minutes'])->toBe('480')
        ->and($zoneTariffs)->not->toHaveKey((string) $vipZone->id)
        ->and($zoneTariffs)->not->toHaveKey((string) $emptyZone->id)
        ->and($zoneTariffs)->not->toHaveKey((string) $otherZone->id);
});

test('guest login QR code setting is saved as a boolean per dashboard', function () {
    [$user, $dashboard] = createUserWithDashboard();
    $this->actingAs($user);

    foreach (['1' => true, '0' => false] as $value => $expected) {
        $this->post('/settings/save', [
            'section' => 'guests',
            'show_qr_code_on_login' => $value,
        ])->assertRedirect();

        expect($dashboard->refresh()->settings['guests']['show_qr_code_on_login'])->toBe($expected);
    }
});

test('guest self transfer setting is saved as a boolean per dashboard', function () {
    [$user, $dashboard] = createUserWithDashboard();
    $this->actingAs($user);

    foreach (['1' => true, '0' => false] as $value => $expected) {
        $this->post('/settings/save', [
            'section' => 'guests',
            'allow_guest_self_transfer' => $value,
        ])->assertRedirect();

        expect($dashboard->refresh()->settings['guests']['allow_guest_self_transfer'])->toBe($expected);
    }
});

test('adding friends setting is saved as a boolean per dashboard', function () {
    [$user, $dashboard] = createUserWithDashboard();
    $this->actingAs($user);

    foreach (['1' => true, '0' => false] as $value => $expected) {
        $this->post('/settings/save', [
            'section' => 'guests',
            'allow_adding_friends' => $value,
        ])->assertRedirect();

        expect($dashboard->refresh()->settings['guests']['allow_adding_friends'])->toBe($expected);
    }
});

test('balance transfer to friends setting is saved as a boolean per dashboard', function () {
    [$user, $dashboard] = createUserWithDashboard();
    $this->actingAs($user);

    foreach (['1' => true, '0' => false] as $value => $expected) {
        $this->post('/settings/save', [
            'section' => 'guests',
            'allow_balance_transfer_to_friends' => $value,
        ])->assertRedirect();

        expect($dashboard->refresh()->settings['guests']['allow_balance_transfer_to_friends'])->toBe($expected);
    }
});

test('API settings page displays every setting with saved dashboard values', function () {
    [$user, $dashboard] = createUserWithDashboard();
    $dashboard->update([
        'settings' => [
            'finances' => [
                'currency' => 'RUB',
            ],
            'guests' => [
                'allow_adding_friends' => true,
            ],
        ],
    ]);

    $this->actingAs($user)
        ->get('/settings/api')
        ->assertSuccessful()
        ->assertViewHas('settings', function (array $settings): bool {
            return $settings['club']['booking_interval'] === '30 минут'
                && $settings['finances']['currency'] === 'RUB'
                && $settings['tariffs']['standard_hour_price'] === '700'
                && $settings['guests']['show_qr_code_on_login'] === false
                && $settings['guests']['allow_guest_self_transfer'] === false
                && $settings['guests']['allow_adding_friends'] === true
                && $settings['guests']['allow_balance_transfer_to_friends'] === false;
        })
        ->assertViewHas('endpoints', fn (array $endpoints): bool => count($endpoints) === 9
            && str_contains($endpoints[0]['url'], '/api/dashboards/'.$dashboard->slug.'/settings/')
            && ! collect($endpoints)->contains(fn (array $endpoint): bool => $endpoint['section'] === 'tariffs'))
        ->assertViewHas('apiRoutes', fn (array $routes): bool => collect($routes)->contains(fn (array $route): bool => $route['name'] === 'api.shell.resolve'
            && $route['methods'] === ['POST']
            && $route['url'] === route('api.shell.resolve'))
            && collect($routes)->contains(fn (array $route): bool => $route['name'] === 'api.auth.login'
                && $route['methods'] === ['POST']
                && $route['url'] === route('api.auth.login'))
            && collect($routes)->contains(fn (array $route): bool => $route['name'] === 'api.products.index'
                && $route['methods'] === ['GET']
                && $route['url'] === route('api.products.index', $dashboard))
            && collect($routes)->contains(fn (array $route): bool => $route['name'] === 'api.tariffs.index'
                && $route['methods'] === ['GET']
                && $route['url'] === route('api.tariffs.index', $dashboard))
            && collect($routes)->contains(fn (array $route): bool => $route['name'] === 'api.settings.show'
                && str_contains($route['url'], '/api/dashboards/'.$dashboard->slug.'/settings/')))
        ->assertViewHas('shellKey', fn (?string $key): bool => str_starts_with($key ?? '', 'club_'))
        ->assertViewHas('shellResolveEndpoint', fn (array $endpoint): bool => $endpoint['url'] === route('api.shell.resolve')
            && $endpoint['request']['shell_key'] === $dashboard->shell_key)
        ->assertViewHas('authEndpoint', fn (array $endpoint): bool => $endpoint['url'] === route('api.auth.login')
            && $endpoint['request']['shell_key'] === $dashboard->shell_key)
        ->assertViewHas('productEndpoint', fn (array $endpoint): bool => $endpoint['url'] === route('api.products.index', $dashboard))
        ->assertViewHas('tariffEndpoint', fn (array $endpoint): bool => $endpoint['url'] === route('api.tariffs.index', $dashboard));
});

test('each setting has its own dashboard JSON API endpoint', function () {
    [, $dashboard] = createUserWithDashboard();
    $dashboard->update([
        'settings' => [
            'guests' => [
                'allow_adding_friends' => true,
            ],
        ],
    ]);

    $this->getJson(route('api.settings.show', [$dashboard, 'guests', 'allow_adding_friends']))
        ->assertSuccessful()
        ->assertJson([
            'dashboard' => $dashboard->slug,
            'setting' => 'guests.allow_adding_friends',
            'name' => 'Возможность добавления в друзья',
            'value' => true,
        ]);

    $this->getJson(route('api.settings.show', [$dashboard, 'guests', 'unknown_setting']))
        ->assertNotFound();
});

test('dashboard tariffs are available through JSON API', function () {
    [$user, $dashboard] = createUserWithDashboard();

    $zone = $dashboard->clubZones()->create([
        'name' => 'VIP зал',
        'description' => 'Игровая зона',
        'icon' => 'desktop',
        'theme' => 'blue',
        'status' => 'available',
        'hourly_price' => 700,
        'is_featured' => true,
        'sort_order' => 10,
    ]);
    $zone->computers()->create(['number' => 1]);

    $dashboard->update([
        'settings' => [
            'finances' => ['currency' => 'RUB'],
            'tariffs' => [
                'zones' => [
                    (string) $zone->id => [
                        ['name' => '1 час', 'price' => '900', 'duration_minutes' => '60'],
                        ['name' => 'Ночь', 'price' => '3500', 'duration_minutes' => '480'],
                    ],
                ],
            ],
        ],
    ]);

    $response = $this->getJson(route('api.tariffs.index', $dashboard));

    $response->assertSuccessful()
        ->assertJsonPath('dashboard', $dashboard->slug)
        ->assertJsonPath('currency', 'RUB')
        ->assertJsonPath('data.0.zone.id', $zone->id)
        ->assertJsonPath('data.0.zone.name', 'VIP зал')
        ->assertJsonPath('data.0.zone.computers_count', 1)
        ->assertJsonPath('data.0.tariffs.0.name', '1 час')
        ->assertJsonPath('data.0.tariffs.0.price', 900)
        ->assertJsonPath('data.0.tariffs.0.currency', 'RUB')
        ->assertJsonPath('data.0.tariffs.1.duration_minutes', 480);

    expect($response->headers->get('Cache-Control'))->toContain('no-store');

    $this->actingAs($user)
        ->get('/settings/api')
        ->assertSuccessful()
        ->assertViewHas('tariffEndpoint', fn (array $endpoint): bool => $endpoint['response']['data'][0]['tariffs'][0]['name'] === '1 час'
            && $endpoint['response']['data'][0]['tariffs'][0]['price'] === 900.0);
});

test('saving tariffs with no rows clears old zone tariffs', function () {
    [$user, $dashboard] = createUserWithDashboard();

    $zone = $dashboard->clubZones()->create([
        'name' => 'VIP зал',
        'description' => 'Игровая зона',
        'icon' => 'desktop',
        'theme' => 'blue',
        'status' => 'available',
        'hourly_price' => 700,
        'is_featured' => true,
        'sort_order' => 10,
    ]);
    $zone->computers()->create(['number' => 1]);

    $dashboard->update([
        'settings' => [
            'tariffs' => [
                'zones' => [
                    (string) $zone->id => [
                        ['name' => 'Старый тариф', 'price' => '900', 'duration_minutes' => '60'],
                    ],
                ],
            ],
        ],
    ]);

    $this->actingAs($user)
        ->post('/settings/save', ['section' => 'tariffs'])
        ->assertRedirect();

    $this->getJson(route('api.tariffs.index', $dashboard))
        ->assertSuccessful()
        ->assertJsonPath('data.0.tariffs', []);
});

test('each dashboard receives a unique shell key', function () {
    [, $firstDashboard] = createUserWithDashboard(['name' => 'First Club']);
    [, $secondDashboard] = createUserWithDashboard(['name' => 'Second Club']);

    expect($firstDashboard->shell_key)->toStartWith('club_')
        ->and($secondDashboard->shell_key)->toStartWith('club_')
        ->and($firstDashboard->shell_key)->not->toBe($secondDashboard->shell_key);
});

test('client shell can resolve dashboard by shell key', function () {
    [, $dashboard] = createUserWithDashboard();

    $this->postJson(route('api.shell.resolve'), ['shell_key' => $dashboard->shell_key])
        ->assertSuccessful()
        ->assertJsonPath('dashboard.id', $dashboard->id)
        ->assertJsonPath('dashboard.slug', $dashboard->slug)
        ->assertJsonPath('api.products_url', route('api.products.index', $dashboard))
        ->assertJsonPath('api.tariffs_url', route('api.tariffs.index', $dashboard));

    $this->postJson(route('api.shell.resolve'), ['shell_key' => 'club_wrong_key'])
        ->assertNotFound();
});

test('club member can login through API with shell key', function () {
    [$owner, $dashboard] = createUserWithDashboard();
    $memberUser = User::factory()->create([
        'name' => 'Shell Client',
        'phone' => '77000000999',
        'email' => 'shell-client@example.com',
        'password' => 'secret123',
    ]);
    $member = $dashboard->clubMembers()->create([
        'user_id' => $memberUser->id,
        'balance' => 1250,
        'bonus_balance' => 300,
        'status' => ClubMember::STATUS_ACTIVE,
    ]);

    $this->postJson(route('api.auth.login'), [
        'shell_key' => $dashboard->shell_key,
        'login' => '77000000999',
        'password' => 'secret123',
    ])->assertSuccessful()
        ->assertJsonPath('dashboard.id', $dashboard->id)
        ->assertJsonPath('member.id', $member->id)
        ->assertJsonPath('member.user.name', 'Shell Client')
        ->assertJsonPath('member.balance', 1250)
        ->assertJsonPath('api.products_url', route('api.products.index', $dashboard));
});

test('club member API login is scoped by shell key', function () {
    [, $dashboard] = createUserWithDashboard(['name' => 'First Club']);
    [, $otherDashboard] = createUserWithDashboard(['name' => 'Second Club']);
    $memberUser = User::factory()->create([
        'phone' => '77000000888',
        'password' => 'secret123',
    ]);
    $dashboard->clubMembers()->create([
        'user_id' => $memberUser->id,
        'status' => ClubMember::STATUS_ACTIVE,
    ]);

    $this->postJson(route('api.auth.login'), [
        'shell_key' => $otherDashboard->shell_key,
        'login' => '77000000888',
        'password' => 'secret123',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('login');
});

test('club member API login rejects wrong password and blocked users', function () {
    [, $dashboard] = createUserWithDashboard();
    $memberUser = User::factory()->create([
        'phone' => '77000000777',
        'password' => 'secret123',
    ]);
    $dashboard->clubMembers()->create([
        'user_id' => $memberUser->id,
        'status' => ClubMember::STATUS_BLOCKED,
    ]);

    $this->postJson(route('api.auth.login'), [
        'shell_key' => $dashboard->shell_key,
        'login' => '77000000777',
        'password' => 'wrong-password',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('login');

    $this->postJson(route('api.auth.login'), [
        'shell_key' => $dashboard->shell_key,
        'login' => '77000000777',
        'password' => 'secret123',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('login');
});
