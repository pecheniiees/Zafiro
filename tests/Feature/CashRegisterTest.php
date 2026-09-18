<?php

use App\Actions\CreateDashboardForUserAction;
use App\Models\CashShift;
use App\Models\CashTransaction;
use App\Models\Dashboard;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createCashAccount(array $userAttributes = []): array
{
    $user = User::factory()->create($userAttributes);
    $dashboard = app(CreateDashboardForUserAction::class)->handle($user);

    return [$user, $dashboard];
}

function createCashProduct(Dashboard $dashboard, array $overrides = []): Product
{
    return Product::factory()->create(['dashboard_id' => $dashboard->id] + $overrides);
}

function createCashShift(Dashboard $dashboard, User $user, array $overrides = []): CashShift
{
    return $dashboard->cashShifts()->create(array_merge([
        'opened_by' => $user->id,
        'status' => CashShift::STATUS_OPEN,
        'open_marker' => true,
        'opening_balance' => 0,
        'opened_at' => now(),
    ], $overrides));
}

test('cash shift can be opened per dashboard and only one can remain open inside same dashboard', function () {
    [$user] = createCashAccount();
    [$otherUser] = createCashAccount(['name' => 'Other Owner']);

    $this->actingAs($user)->post('/cash-register/shifts', ['opening_balance' => 10000])->assertRedirect();
    expect(CashShift::query()->where('status', 'open')->count())->toBe(1);

    $this->actingAs($user)->post('/cash-register/shifts', ['opening_balance' => 5000])->assertSessionHasErrors('opening_balance');

    $this->actingAs($otherUser)->post('/cash-register/shifts', ['opening_balance' => 5000])->assertRedirect();
    expect(CashShift::query()->where('status', 'open')->count())->toBe(2);
});

test('cash operations are included in own X report', function () {
    [$user, $dashboard] = createCashAccount();
    createCashShift($dashboard, $user, ['opening_balance' => 10000]);

    [$otherUser, $otherDashboard] = createCashAccount(['name' => 'Other Owner']);
    $otherShift = createCashShift($otherDashboard, $otherUser, ['opening_balance' => 90000]);
    $otherShift->transactions()->create([
        'dashboard_id' => $otherDashboard->id,
        'user_id' => $otherUser->id,
        'type' => CashTransaction::TYPE_INCOME,
        'amount' => 50000,
        'description' => 'Чужая продажа',
    ]);

    $this->actingAs($user)->post('/cash-register/transactions', ['type' => 'income', 'amount' => 3000, 'description' => 'Продажа'])->assertRedirect();
    $this->actingAs($user)->post('/cash-register/transactions', ['type' => 'expense', 'amount' => 1000, 'description' => 'Расход'])->assertRedirect();

    expect($dashboard->cashTransactions()->count())->toBe(2)
        ->and($otherDashboard->cashTransactions()->count())->toBe(1);

    $this->actingAs($user)
        ->get('/cash-register/reports/x')
        ->assertSuccessful()
        ->assertSee('12 000,00 KZT')
        ->assertDontSee('140 000,00 KZT');
});

test('Z report must be printed before the own shift can be closed', function () {
    [$user, $dashboard] = createCashAccount();
    $shift = createCashShift($dashboard, $user, ['opening_balance' => 5000]);
    $shift->transactions()->create([
        'dashboard_id' => $dashboard->id,
        'user_id' => $user->id,
        'type' => CashTransaction::TYPE_INCOME,
        'amount' => 2000,
        'description' => 'Продажа',
    ]);

    $this->actingAs($user)->post("/cash-register/shifts/{$shift->id}/close")
        ->assertRedirect(route('cash-register.z-report', $shift))
        ->assertSessionHasErrors('shift');
    expect($shift->refresh()->status)->toBe('open');

    $this->actingAs($user)->get("/cash-register/shifts/{$shift->id}/z-report")
        ->assertSuccessful()
        ->assertSee('Z-отчёт');

    $this->withSession(["z_report_printed.{$shift->id}" => true])
        ->post("/cash-register/shifts/{$shift->id}/close")
        ->assertRedirect(route('cash-register.z-report', $shift));
    expect($shift->refresh()->status)->toBe('closed')->and($shift->closing_balance)->toBe('7000.00');
});

test('user cannot view or close another dashboard shift', function () {
    [$user] = createCashAccount();
    [$otherUser, $otherDashboard] = createCashAccount(['name' => 'Other Owner']);
    $shift = createCashShift($otherDashboard, $otherUser, ['opening_balance' => 5000]);

    $this->actingAs($user)->get(route('cash-register.z-report', $shift))->assertNotFound();

    $this->withSession(["z_report_printed.{$shift->id}" => true])
        ->actingAs($user)
        ->post(route('cash-register.shifts.close', $shift))
        ->assertNotFound();

    expect($shift->refresh()->status)->toBe(CashShift::STATUS_OPEN);
});

test('POS sale writes off own products and records own cash income', function () {
    [$user, $dashboard] = createCashAccount();
    createCashShift($dashboard, $user, ['opening_balance' => 0]);
    $product = createCashProduct($dashboard, ['price' => 750, 'quantity' => 5]);

    $response = $this->actingAs($user)->post('/cash-register/sales', ['items' => [
        ['product_id' => $product->id, 'quantity' => 2],
    ]]);

    $transaction = $dashboard->cashTransactions()->first();
    $response->assertRedirect(route('cash-register.receipt', $transaction))->assertSessionHas('status');
    $this->actingAs($user)->get(route('cash-register.receipt', $transaction))
        ->assertSuccessful()
        ->assertSee($product->name)
        ->assertSee('Распечатать чек');

    expect($product->refresh()->quantity)->toBe(3)
        ->and($transaction->amount)->toBe('1500.00')
        ->and($transaction->receipt_data['items'][0]['quantity'])->toBe(2)
        ->and($dashboard->stockMovements()->first()->balance_after)->toBe(3);
});

test('POS sale cannot use another dashboard product', function () {
    [$user, $dashboard] = createCashAccount();
    createCashShift($dashboard, $user, ['opening_balance' => 0]);

    [, $otherDashboard] = createCashAccount(['name' => 'Other Owner']);
    $product = createCashProduct($otherDashboard, ['price' => 750, 'quantity' => 5]);

    $this->actingAs($user)
        ->from('/cash-register')
        ->post('/cash-register/sales', ['items' => [
            ['product_id' => $product->id, 'quantity' => 2],
        ]])
        ->assertRedirect('/cash-register')
        ->assertSessionHasErrors('items');

    expect($product->refresh()->quantity)->toBe(5)
        ->and(StockMovement::query()->count())->toBe(0)
        ->and(CashTransaction::query()->count())->toBe(0);
});

test('POS page displays only own products when shift is open', function () {
    [$user, $dashboard] = createCashAccount();
    createCashShift($dashboard, $user, ['opening_balance' => 0]);
    createCashProduct($dashboard, ['name' => 'POS Test Product', 'quantity' => 2]);

    [, $otherDashboard] = createCashAccount(['name' => 'Other Owner']);
    createCashProduct($otherDashboard, ['name' => 'Other POS Product', 'quantity' => 2]);

    $this->actingAs($user)
        ->get('/cash-register')
        ->assertSuccessful()
        ->assertSee('POS Test Product')
        ->assertSee('Оплатить')
        ->assertDontSee('Other POS Product');
});
