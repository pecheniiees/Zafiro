<?php

use App\Actions\CreateDashboardForUserAction;
use App\Models\ClubComputer;
use App\Models\ClubZone;
use App\Models\Dashboard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createClubZoneForDashboard(Dashboard $dashboard, array $overrides = []): ClubZone
{
    $zone = $dashboard->clubZones()->create(array_merge([
        'name' => 'Турнирный зал',
        'description' => 'Зона для соревнований',
        'icon' => 'desktop',
        'theme' => 'blue',
        'status' => 'available',
        'hourly_price' => 700,
        'is_featured' => true,
        'sort_order' => 15,
        'position_x' => 20,
        'position_y' => 20,
        'width' => 320,
        'height' => 180,
    ], $overrides));

    return $zone;
}

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->dashboard = app(CreateDashboardForUserAction::class)->handle($this->user);
    $this->actingAs($this->user);
    $this->zoneData = [
        'name' => 'Турнирный зал',
        'description' => 'Зона для соревнований',
        'icon' => 'desktop',
        'theme' => 'blue',
        'status' => 'available',
        'hourly_price' => 700,
        'is_featured' => true,
        'sort_order' => 15,
    ];
});

test('user can create a club zone in own dashboard', function () {
    $this->post(route('club-map.store'), $this->zoneData)
        ->assertRedirect()
        ->assertSessionHas('status');

    $zone = ClubZone::query()->firstOrFail();
    $this->assertModelExists($zone);
    expect($zone->dashboard_id)->toBe($this->dashboard->id)
        ->and($zone->hourly_price)->toBe('700.00')
        ->and($zone->computers()->count())->toBe(0);
});


test('computer club zone requires an hourly price', function () {
    $payload = $this->zoneData;
    unset($payload['hourly_price']);

    $this->post(route('club-map.store'), $payload)
        ->assertSessionHasErrors('hourly_price');
});

test('non computer club zone does not keep hourly price', function () {
    $this->post(route('club-map.store'), [...$this->zoneData, 'icon' => 'crown', 'hourly_price' => 900])
        ->assertRedirect()
        ->assertSessionHas('status');

    expect(ClubZone::query()->firstOrFail()->hourly_price)->toBeNull();
});

test('user can add computers manually to own club zone', function () {
    $zone = createClubZoneForDashboard($this->dashboard);

    $this->post(route('club-map.computers.store', $zone))
        ->assertRedirect()
        ->assertSessionHas('status');

    $this->post(route('club-map.computers.store', $zone))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($zone->computers()->pluck('number')->all())->toBe([1, 2])
        ->and($zone->computers()->pluck('status')->all())->toBe([ClubComputer::STATUS_OFF, ClubComputer::STATUS_OFF]);
});

test('user cannot add computers to another dashboard club zone', function () {
    $otherUser = User::factory()->create();
    $otherDashboard = app(CreateDashboardForUserAction::class)->handle($otherUser);
    $zone = createClubZoneForDashboard($otherDashboard);

    $this->post(route('club-map.computers.store', $zone))->assertNotFound();

    expect($zone->computers()->count())->toBe(0);
});

test('user can see only own created club zone', function () {
    createClubZoneForDashboard($this->dashboard, ['name' => 'Моя зона']);

    $otherUser = User::factory()->create();
    $otherDashboard = app(CreateDashboardForUserAction::class)->handle($otherUser);
    createClubZoneForDashboard($otherDashboard, ['name' => 'Чужая зона']);

    $this->get(route('club-map.index'))
        ->assertSuccessful()
        ->assertSee('Моя зона')
        ->assertDontSee('Чужая зона');
});

test('new user starts with an empty club map when another dashboard has zones', function () {
    createClubZoneForDashboard($this->dashboard, ['name' => 'Карта первого аккаунта']);

    $newUser = User::factory()->create();
    app(CreateDashboardForUserAction::class)->handle($newUser);

    $this->actingAs($newUser)
        ->get(route('club-map.index'))
        ->assertSuccessful()
        ->assertDontSee('Карта первого аккаунта')
        ->assertViewHas('zones', fn ($zones): bool => $zones->isEmpty());
});

test('user can update own club zone', function () {
    $zone = createClubZoneForDashboard($this->dashboard);

    $this->put(route('club-map.update', $zone), [...$this->zoneData, 'name' => 'Главный зал', 'status' => 'busy'])
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($zone->refresh()->name)->toBe('Главный зал')
        ->and($zone->status)->toBe('busy');
});

test('user cannot update another dashboard club zone', function () {
    $otherUser = User::factory()->create();
    $otherDashboard = app(CreateDashboardForUserAction::class)->handle($otherUser);
    $zone = createClubZoneForDashboard($otherDashboard, ['name' => 'Чужая зона']);

    $this->put(route('club-map.update', $zone), [...$this->zoneData, 'name' => 'Захваченная зона'])
        ->assertNotFound();

    expect($zone->refresh()->name)->toBe('Чужая зона');
});

test('user can update own club zone layout', function () {
    $zone = createClubZoneForDashboard($this->dashboard);

    $this->patchJson(route('club-map.layout', $zone), [
        'position_x' => 240,
        'position_y' => 160,
        'width' => 480,
        'height' => 260,
    ])->assertSuccessful()->assertJson(['saved' => true]);

    expect($zone->refresh()->position_x)->toBe(240)
        ->and($zone->position_y)->toBe(160)
        ->and($zone->width)->toBe(480)
        ->and($zone->height)->toBe(260);
});

test('club zones cannot overlap inside same dashboard', function () {
    $zone = createClubZoneForDashboard($this->dashboard, ['position_x' => 240]);
    createClubZoneForDashboard($this->dashboard, ['position_x' => 813, 'position_y' => 507, 'width' => 300, 'height' => 180]);

    $this->patchJson(route('club-map.layout', $zone), [
        'position_x' => 820,
        'position_y' => 520,
        'width' => 300,
        'height' => 180,
    ])->assertConflict()->assertJson(['message' => 'Карточки залов не могут пересекаться.']);

    expect($zone->refresh()->position_x)->toBe(240);
});

test('club zone overlap check ignores another dashboard zones', function () {
    $zone = createClubZoneForDashboard($this->dashboard, ['position_x' => 240]);

    $otherUser = User::factory()->create();
    $otherDashboard = app(CreateDashboardForUserAction::class)->handle($otherUser);
    createClubZoneForDashboard($otherDashboard, ['position_x' => 813, 'position_y' => 507, 'width' => 300, 'height' => 180]);

    $this->patchJson(route('club-map.layout', $zone), [
        'position_x' => 820,
        'position_y' => 520,
        'width' => 300,
        'height' => 180,
    ])->assertSuccessful()->assertJson(['saved' => true]);

    expect($zone->refresh()->position_x)->toBe(820);
});

test('user cannot update another dashboard club zone layout', function () {
    $otherUser = User::factory()->create();
    $otherDashboard = app(CreateDashboardForUserAction::class)->handle($otherUser);
    $zone = createClubZoneForDashboard($otherDashboard, ['position_x' => 240]);

    $this->patchJson(route('club-map.layout', $zone), [
        'position_x' => 125,
        'position_y' => 75,
        'width' => 320,
        'height' => 180,
    ])->assertNotFound();

    expect($zone->refresh()->position_x)->toBe(240);
});


test('user can update own computer details', function () {
    $zone = createClubZoneForDashboard($this->dashboard);
    $computer = $zone->computers()->create(['number' => 1]);

    $this->put(route('club-computers.update', $computer), [
        'name' => 'VIP-01',
        'inventory_number' => 'INV-777',
        'ip_address' => '192.168.1.77',
        'specs' => 'RTX 4070, i7, 32 GB RAM',
        'note' => 'Left monitor needs calibration',
        'status' => ClubComputer::STATUS_ON,
    ])->assertRedirect()->assertSessionHas('status');

    expect($computer->refresh()->name)->toBe('VIP-01')
        ->and($computer->inventory_number)->toBe('INV-777')
        ->and($computer->ip_address)->toBe('192.168.1.77')
        ->and($computer->specs)->toBe('RTX 4070, i7, 32 GB RAM')
        ->and($computer->note)->toBe('Left monitor needs calibration')
        ->and($computer->status)->toBe(ClubComputer::STATUS_ON);
});

test('user cannot update another dashboard computer details', function () {
    $otherUser = User::factory()->create();
    $otherDashboard = app(CreateDashboardForUserAction::class)->handle($otherUser);
    $zone = createClubZoneForDashboard($otherDashboard);
    $computer = $zone->computers()->create(['number' => 1]);

    $this->put(route('club-computers.update', $computer), [
        'name' => 'Captured PC',
        'inventory_number' => 'INV-777',
        'ip_address' => '192.168.1.77',
        'specs' => 'RTX 4070',
        'note' => 'Should not save',
        'status' => ClubComputer::STATUS_ON,
    ])->assertNotFound();

    expect($computer->refresh()->name)->toBeNull()
        ->and($computer->status)->toBe(ClubComputer::STATUS_OFF);
});

test('user can update own computer layout', function () {
    $zone = createClubZoneForDashboard($this->dashboard);
    $computer = $zone->computers()->create(['number' => 1]);

    $this->patchJson(route('club-computers.layout', $computer), [
        'position_x' => 125,
        'position_y' => 75,
    ])->assertSuccessful()->assertJson(['saved' => true]);

    expect($computer->refresh()->position_x)->toBe(125)
        ->and($computer->position_y)->toBe(75);
});

test('computer positions cannot overlap inside same zone', function () {
    $zone = createClubZoneForDashboard($this->dashboard);
    $computer = $zone->computers()->create(['number' => 1, 'position_x' => 125, 'position_y' => 75]);
    $secondComputer = $zone->computers()->create(['number' => 2, 'position_x' => 5, 'position_y' => 5]);

    $this->patchJson(route('club-computers.layout', $secondComputer), [
        'position_x' => 125,
        'position_y' => 75,
    ])->assertConflict()->assertJson(['message' => 'Эта ячейка уже занята другим компьютером.']);

    expect($secondComputer->refresh()->position_x)->not->toBe(125);
});

test('user cannot update another dashboard computer layout', function () {
    $otherUser = User::factory()->create();
    $otherDashboard = app(CreateDashboardForUserAction::class)->handle($otherUser);
    $zone = createClubZoneForDashboard($otherDashboard);
    $computer = $zone->computers()->create(['number' => 1]);

    $this->patchJson(route('club-computers.layout', $computer), [
        'position_x' => 125,
        'position_y' => 75,
    ])->assertNotFound();

    expect($computer->refresh()->position_x)->not->toBe(125);
});

test('user can control multiple own computers', function () {
    $zone = createClubZoneForDashboard($this->dashboard);
    $zone->computers()->createMany([['number' => 1], ['number' => 2]]);
    $computers = $zone->refresh()->computers;

    $this->postJson(route('club-computers.control'), [
        'computer_ids' => $computers->pluck('id')->all(),
        'action' => 'reserve',
    ])->assertSuccessful()->assertJson(['updated' => 2, 'status' => ClubComputer::STATUS_RESERVED]);

    expect($computers[0]->refresh()->status)->toBe(ClubComputer::STATUS_RESERVED)
        ->and($computers[1]->refresh()->status)->toBe(ClubComputer::STATUS_RESERVED);
});

test('user cannot control another dashboard computers', function () {
    $otherUser = User::factory()->create();
    $otherDashboard = app(CreateDashboardForUserAction::class)->handle($otherUser);
    $zone = createClubZoneForDashboard($otherDashboard);
    $zone->computers()->createMany([['number' => 1], ['number' => 2]]);
    $computers = $zone->refresh()->computers;

    $this->postJson(route('club-computers.control'), [
        'computer_ids' => $computers->pluck('id')->all(),
        'action' => 'maintenance',
    ])->assertSuccessful()->assertJson(['updated' => 0, 'status' => ClubComputer::STATUS_MAINTENANCE]);

    expect($computers[0]->refresh()->status)->toBe(ClubComputer::STATUS_OFF)
        ->and($computers[1]->refresh()->status)->toBe(ClubComputer::STATUS_OFF);
});

test('user can delete own club zone', function () {
    $zone = createClubZoneForDashboard($this->dashboard);

    $this->delete(route('club-map.destroy', $zone))
        ->assertRedirect()
        ->assertSessionHas('status');

    $this->assertModelMissing($zone);
});

test('user cannot delete another dashboard club zone', function () {
    $otherUser = User::factory()->create();
    $otherDashboard = app(CreateDashboardForUserAction::class)->handle($otherUser);
    $zone = createClubZoneForDashboard($otherDashboard);

    $this->delete(route('club-map.destroy', $zone))->assertNotFound();

    $this->assertModelExists($zone);
});

test('club zone input is validated', function () {
    $this->post(route('club-map.store'), [
        'name' => '',
        'icon' => 'script-tag',
        'theme' => 'unknown',
        'status' => 'unknown',
        'is_featured' => false,
        'sort_order' => -1,
    ])->assertSessionHasErrors(['name', 'icon', 'theme', 'status', 'sort_order']);

    expect(ClubZone::query()->count())->toBe(0);
});
