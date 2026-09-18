<?php

use App\Models\Dashboard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('registration creates a dashboard for the new user', function () {
    $this->post('/register', [
        'name' => 'Zafiro Owner',
        'phone' => '+77770000001',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticated();

    $user = User::where('phone', '+77770000001')->firstOrFail();

    expect($user->dashboard)
        ->not->toBeNull()
        ->name->toBe('Zafiro Owner dashboard')
        ->slug->toBe('zafiro-owner-'.$user->id)
        ->plan->toBe(Dashboard::PLAN_STARTER)
        ->status->toBe(Dashboard::STATUS_ACTIVE);

    $this->assertDatabaseHas('dashboards', [
        'user_id' => $user->id,
        'name' => 'Zafiro Owner dashboard',
        'slug' => 'zafiro-owner-'.$user->id,
        'plan' => Dashboard::PLAN_STARTER,
        'status' => Dashboard::STATUS_ACTIVE,
    ]);
});

test('dashboard page creates a missing dashboard for existing users', function () {
    $user = User::factory()->create(['name' => 'Existing Owner']);

    expect($user->dashboard)->toBeNull();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertSuccessful()
        ->assertViewHas('dashboard', function (Dashboard $dashboard) use ($user): bool {
            return $dashboard->user_id === $user->id
                && $dashboard->name === 'Existing Owner dashboard'
                && $dashboard->slug === 'existing-owner-'.$user->id;
        })
        ->assertSee('Existing Owner dashboard');

    $this->assertDatabaseHas('dashboards', [
        'user_id' => $user->id,
        'name' => 'Existing Owner dashboard',
        'slug' => 'existing-owner-'.$user->id,
    ]);
});

test('dashboard page shows current dashboard metrics only', function () {
    $owner = User::factory()->create(['name' => 'Metric Owner']);
    $dashboard = app(App\Actions\CreateDashboardForUserAction::class)->handle($owner);
    $memberUser = User::factory()->create(['name' => 'Own Member']);
    $dashboard->clubMembers()->create(['user_id' => $memberUser->id, 'balance' => 1500, 'bonus_balance' => 100]);
    $shift = $dashboard->cashShifts()->create(['status' => 'open', 'opening_balance' => 0, 'opened_at' => now()]);
    $dashboard->cashTransactions()->create(['cash_shift_id' => $shift->id, 'type' => 'income', 'amount' => 2500, 'description' => 'Own sale']);
    $dashboard->cashTransactions()->create(['cash_shift_id' => $shift->id, 'type' => 'expense', 'amount' => 500, 'description' => 'Own expense']);
    $product = $dashboard->products()->create(['sku' => 'LOW-001', 'name' => 'Own Low Product', 'category' => 'Test', 'price' => 100, 'quantity' => 3]);
    $dashboard->stockMovements()->create(['product_id' => $product->id, 'type' => 'receipt', 'quantity' => 2, 'balance_after' => 3]);

    $otherOwner = User::factory()->create(['name' => 'Other Owner']);
    $otherDashboard = app(App\Actions\CreateDashboardForUserAction::class)->handle($otherOwner);
    $otherShift = $otherDashboard->cashShifts()->create(['status' => 'open', 'opening_balance' => 0, 'opened_at' => now()]);
    $otherDashboard->cashTransactions()->create(['cash_shift_id' => $otherShift->id, 'type' => 'income', 'amount' => 9999, 'description' => 'Other sale']);
    $otherDashboard->products()->create(['sku' => 'OTH-001', 'name' => 'Other Low Product', 'category' => 'Test', 'price' => 100, 'quantity' => 1]);

    $this->actingAs($owner)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('2 500 KZT')
        ->assertSee('2 000 KZT')
        ->assertSee('Own sale')
        ->assertSee('Own Low Product')
        ->assertDontSee('Other sale')
        ->assertDontSee('Other Low Product')
        ->assertViewHas('stats', fn (array $stats): bool => $stats['club_members'] === 1 && $stats['member_balance'] === 1500.0);
});
