<?php

use App\Actions\CreateDashboardForUserAction;
use App\Models\Dashboard;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createWarehouseAccount(array $userAttributes = []): array
{
    $user = User::factory()->create($userAttributes);
    $dashboard = app(CreateDashboardForUserAction::class)->handle($user);

    return [$user, $dashboard];
}

function createDashboardProduct(Dashboard $dashboard, array $overrides = []): Product
{
    return Product::factory()->create(['dashboard_id' => $dashboard->id] + $overrides);
}

test('authenticated user can open warehouse page with only own products', function () {
    [$user, $dashboard] = createWarehouseAccount();
    createDashboardProduct($dashboard, ['sku' => 'OWN-001', 'name' => 'Coca-Cola 0,5 л']);
    createDashboardProduct($dashboard, ['sku' => 'OWN-002', 'name' => 'Кабель HDMI 2 м']);

    [, $otherDashboard] = createWarehouseAccount(['name' => 'Other Owner']);
    createDashboardProduct($otherDashboard, ['sku' => 'OTHER-001', 'name' => 'Чужой товар']);

    $this->actingAs($user)
        ->get('/warehouse')
        ->assertSuccessful()
        ->assertSee('Склад')
        ->assertSee('Товаров на складе: 2')
        ->assertSee('images/product-placeholder.svg')
        ->assertSee('Coca-Cola 0,5 л')
        ->assertSee('Кабель HDMI 2 м')
        ->assertDontSee('Чужой товар');
});

test('product can be received into own stock', function () {
    [$user, $dashboard] = createWarehouseAccount();
    $product = createDashboardProduct($dashboard, ['quantity' => 10]);

    $this->actingAs($user)
        ->post('/warehouse/stock-movements', [
            'product_id' => $product->id,
            'type' => StockMovement::TYPE_RECEIPT,
            'quantity' => 7,
            'note' => 'Поставка',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($product->refresh()->quantity)->toBe(17);
    expect(StockMovement::query()->first())
        ->dashboard_id->toBe($dashboard->id)
        ->type->toBe(StockMovement::TYPE_RECEIPT)
        ->quantity->toBe(7)
        ->balance_after->toBe(17)
        ->note->toBe('Поставка');
});

test('product can be written off from own stock', function () {
    [$user, $dashboard] = createWarehouseAccount();
    $product = createDashboardProduct($dashboard, ['quantity' => 10]);

    $this->actingAs($user)
        ->post('/warehouse/stock-movements', [
            'product_id' => $product->id,
            'type' => StockMovement::TYPE_WRITE_OFF,
            'quantity' => 4,
        ])
        ->assertRedirect();

    expect($product->refresh()->quantity)->toBe(6);
    expect(StockMovement::query()->first())
        ->dashboard_id->toBe($dashboard->id)
        ->type->toBe(StockMovement::TYPE_WRITE_OFF)
        ->balance_after->toBe(6);
});

test('user cannot adjust another dashboard product stock', function () {
    [$user] = createWarehouseAccount();
    [, $otherDashboard] = createWarehouseAccount(['name' => 'Other Owner']);
    $product = createDashboardProduct($otherDashboard, ['quantity' => 10]);

    $this->actingAs($user)
        ->from('/warehouse')
        ->post('/warehouse/stock-movements', [
            'product_id' => $product->id,
            'type' => StockMovement::TYPE_RECEIPT,
            'quantity' => 7,
        ])
        ->assertRedirect('/warehouse')
        ->assertSessionHasErrors('product_id');

    expect($product->refresh()->quantity)->toBe(10);
    expect(StockMovement::query()->count())->toBe(0);
});

test('product cannot be written off beyond available stock', function () {
    [$user, $dashboard] = createWarehouseAccount();
    $product = createDashboardProduct($dashboard, ['quantity' => 3]);

    $this->actingAs($user)
        ->from('/warehouse')
        ->post('/warehouse/stock-movements', [
            'product_id' => $product->id,
            'type' => StockMovement::TYPE_WRITE_OFF,
            'quantity' => 4,
        ])
        ->assertRedirect('/warehouse')
        ->assertSessionHasErrors('quantity');

    expect($product->refresh()->quantity)->toBe(3);
    expect(StockMovement::query()->count())->toBe(0);
});

test('mobile API returns only dashboard products with image stock and dashboard currency', function () {
    [, $dashboard] = createWarehouseAccount();
    $dashboard->update(['settings' => ['finances' => ['currency' => 'RUB']]]);
    createDashboardProduct($dashboard, [
        'sku' => 'MOB-001',
        'name' => 'Мобильный товар',
        'price' => 1500,
        'quantity' => 5,
    ]);

    [, $otherDashboard] = createWarehouseAccount(['name' => 'Other Owner']);
    createDashboardProduct($otherDashboard, ['sku' => 'MOB-002', 'name' => 'Чужой мобильный товар']);

    $this->getJson(route('api.products.index', $dashboard))
        ->assertSuccessful()
        ->assertJsonPath('data.0.sku', 'MOB-001')
        ->assertJsonPath('data.0.name', 'Мобильный товар')
        ->assertJsonPath('data.0.price', 1500)
        ->assertJsonPath('data.0.currency', 'RUB')
        ->assertJsonPath('data.0.quantity', 5)
        ->assertJsonPath('data.0.in_stock', true)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonMissing(['name' => 'Чужой мобильный товар']);
});
