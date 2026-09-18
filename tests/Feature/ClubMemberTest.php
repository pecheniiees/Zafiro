<?php

use App\Actions\CreateDashboardForUserAction;
use App\Models\ClubMember;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('club member links a dashboard and user with balances and status', function () {
    $owner = User::factory()->create();
    $memberUser = User::factory()->create();
    $dashboard = app(CreateDashboardForUserAction::class)->handle($owner);

    $member = $dashboard->clubMembers()->create([
        'user_id' => $memberUser->id,
        'balance' => 1250,
        'bonus_balance' => 300,
        'status' => ClubMember::STATUS_ACTIVE,
    ]);

    expect($member->dashboard->is($dashboard))->toBeTrue()
        ->and($member->user->is($memberUser))->toBeTrue()
        ->and($dashboard->clubMembers()->whereBelongsTo($memberUser, 'user')->exists())->toBeTrue()
        ->and($memberUser->clubMemberships()->whereBelongsTo($dashboard)->exists())->toBeTrue()
        ->and($member->balance)->toBe('1250.00')
        ->and($member->bonus_balance)->toBe('300.00')
        ->and($member->status)->toBe(ClubMember::STATUS_ACTIVE);
});

test('club member has default balances and active status', function () {
    $owner = User::factory()->create();
    $memberUser = User::factory()->create();
    $dashboard = app(CreateDashboardForUserAction::class)->handle($owner);

    $member = $dashboard->clubMembers()->create(['user_id' => $memberUser->id]);

    expect($member->refresh()->balance)->toBe('0.00')
        ->and($member->bonus_balance)->toBe('0.00')
        ->and($member->status)->toBe(ClubMember::STATUS_ACTIVE);
});

test('user can be linked only once to the same dashboard', function () {
    $owner = User::factory()->create();
    $memberUser = User::factory()->create();
    $dashboard = app(CreateDashboardForUserAction::class)->handle($owner);

    $dashboard->clubMembers()->create(['user_id' => $memberUser->id]);

    expect(fn () => $dashboard->clubMembers()->create(['user_id' => $memberUser->id]))
        ->toThrow(UniqueConstraintViolationException::class);
});


test('club members page shows only current dashboard users', function () {
    $owner = User::factory()->create();
    $dashboard = app(CreateDashboardForUserAction::class)->handle($owner);
    $ownMemberUser = User::factory()->create(['name' => 'Own Club User', 'phone' => '77000000001']);
    $dashboard->clubMembers()->create([
        'user_id' => $ownMemberUser->id,
        'balance' => 2500,
        'bonus_balance' => 150,
        'status' => ClubMember::STATUS_ACTIVE,
    ]);

    $otherOwner = User::factory()->create();
    $otherDashboard = app(CreateDashboardForUserAction::class)->handle($otherOwner);
    $otherMemberUser = User::factory()->create(['name' => 'Other Club User', 'phone' => '77000000002']);
    $otherDashboard->clubMembers()->create([
        'user_id' => $otherMemberUser->id,
        'balance' => 9999,
        'bonus_balance' => 999,
        'status' => ClubMember::STATUS_BLOCKED,
    ]);

    $this->actingAs($owner)
        ->get(route('club-members.index'))
        ->assertSuccessful()
        ->assertSee('Пользователи клуба')
        ->assertSee('Own Club User')
        ->assertSee('2 500 KZT')
        ->assertDontSee('Other Club User')
        ->assertDontSee('9 999 KZT');
});

test('club members menu item is visible in admin sidebar', function () {
    $owner = User::factory()->create();
    app(CreateDashboardForUserAction::class)->handle($owner);

    $this->actingAs($owner)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee(route('club-members.index'))
        ->assertSee('Пользователи');
});


test('club owner can create a user for current dashboard', function () {
    $owner = User::factory()->create();
    $dashboard = app(CreateDashboardForUserAction::class)->handle($owner);

    $this->actingAs($owner)
        ->post(route('club-members.store'), [
            'name' => 'New Club User',
            'phone' => '77000000111',
            'email' => 'new-user@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'balance' => 4500,
            'bonus_balance' => 250,
            'status' => ClubMember::STATUS_ACTIVE,
        ])
        ->assertRedirect(route('club-members.index'))
        ->assertSessionHas('status');

    $user = User::query()->where('phone', '77000000111')->firstOrFail();

    expect($user->name)->toBe('New Club User')
        ->and($user->email)->toBe('new-user@example.com')
        ->and($dashboard->clubMembers()->whereBelongsTo($user, 'user')->exists())->toBeTrue()
        ->and($dashboard->clubMembers()->whereBelongsTo($user, 'user')->first()->balance)->toBe('4500.00')
        ->and($dashboard->clubMembers()->whereBelongsTo($user, 'user')->first()->bonus_balance)->toBe('250.00');
});

test('created club user is visible only in creator dashboard members page', function () {
    $owner = User::factory()->create();
    app(CreateDashboardForUserAction::class)->handle($owner);

    $otherOwner = User::factory()->create();
    app(CreateDashboardForUserAction::class)->handle($otherOwner);

    $this->actingAs($owner)->post(route('club-members.store'), [
        'name' => 'Tenant Only User',
        'phone' => '77000000112',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
        'status' => ClubMember::STATUS_ACTIVE,
    ]);

    $this->actingAs($owner)
        ->get(route('club-members.index'))
        ->assertSuccessful()
        ->assertSee('Tenant Only User');

    $this->actingAs($otherOwner)
        ->get(route('club-members.index'))
        ->assertSuccessful()
        ->assertDontSee('Tenant Only User');
});

test('club user creation validates unique phone', function () {
    $owner = User::factory()->create();
    app(CreateDashboardForUserAction::class)->handle($owner);
    User::factory()->create(['phone' => '77000000113']);

    $this->actingAs($owner)
        ->from(route('club-members.index'))
        ->post(route('club-members.store'), [
            'name' => 'Duplicate Phone User',
            'phone' => '77000000113',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'status' => ClubMember::STATUS_ACTIVE,
        ])
        ->assertRedirect(route('club-members.index'))
        ->assertSessionHasErrors('phone');
});
