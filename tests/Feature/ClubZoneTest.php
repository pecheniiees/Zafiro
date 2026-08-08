<?php

use App\Models\ClubZone;
use App\Models\ClubComputer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can manage club map zones', function () {
    $user = User::factory()->create();
    $data = [
        'name' => 'Турнирный зал',
        'description' => 'Зона для соревнований',
        'capacity' => 12,
        'icon' => 'desktop',
        'theme' => 'blue',
        'status' => 'available',
        'is_featured' => true,
        'sort_order' => 15,
    ];

    $this->actingAs($user)->post(route('club-map.store'), $data)
        ->assertRedirect()
        ->assertSessionHas('status');

    $zone = ClubZone::query()->firstOrFail();
    $this->assertModelExists($zone);
    expect($zone->computers()->count())->toBe(12);
    $this->actingAs($user)->get(route('club-map.index'))
        ->assertSuccessful()
        ->assertSee('Турнирный зал');

    $this->actingAs($user)->put(route('club-map.update', $zone), [...$data, 'name' => 'Главный зал', 'status' => 'busy'])
        ->assertRedirect()
        ->assertSessionHas('status');
    expect($zone->refresh()->name)->toBe('Главный зал')
        ->and($zone->status)->toBe('busy');

    $this->actingAs($user)->patchJson(route('club-map.layout', $zone), [
        'position_x' => 240,
        'position_y' => 160,
        'width' => 480,
        'height' => 260,
    ])->assertSuccessful()->assertJson(['saved' => true]);
    expect($zone->refresh()->position_x)->toBe(240)
        ->and($zone->position_y)->toBe(160)
        ->and($zone->width)->toBe(480)
        ->and($zone->height)->toBe(260);

    $otherZone = ClubZone::query()->create([
        'name' => 'Соседний зал',
        'position_x' => 813,
        'position_y' => 507,
        'width' => 300,
        'height' => 180,
    ]);
    $this->actingAs($user)->patchJson(route('club-map.layout', $zone), [
        'position_x' => 820,
        'position_y' => 520,
        'width' => 300,
        'height' => 180,
    ])->assertConflict()->assertJson(['message' => 'Карточки залов не могут пересекаться.']);
    expect($zone->refresh()->position_x)->toBe(240);

    $this->actingAs($user)->post(route('club-map.align'))->assertRedirect()->assertSessionHas('status');
    expect($zone->refresh()->position_x)->toBe(240)
        ->and($zone->position_y)->toBe(160)
        ->and($otherZone->refresh()->position_x)->toBe(820)
        ->and($otherZone->position_y)->toBe(500);

    $computer = ClubComputer::query()->firstOrFail();
    $this->actingAs($user)->patchJson(route('club-computers.layout', $computer), [
        'position_x' => 125,
        'position_y' => 75,
    ])->assertSuccessful()->assertJson(['saved' => true]);
    expect($computer->refresh()->position_x)->toBe(125)
        ->and($computer->position_y)->toBe(75);

    $secondComputer = ClubComputer::query()->whereKeyNot($computer->id)->firstOrFail();
    $this->actingAs($user)->patchJson(route('club-computers.layout', $secondComputer), [
        'position_x' => 125,
        'position_y' => 75,
    ])->assertConflict()->assertJson(['message' => 'Эта ячейка уже занята другим компьютером.']);
    expect($secondComputer->refresh()->position_x)->not->toBe(125);

    $this->actingAs($user)->postJson(route('club-computers.control'), [
        'computer_ids' => [$computer->id, $secondComputer->id],
        'action' => 'power_off',
    ])->assertSuccessful()->assertJson(['updated' => 2, 'status' => 'offline']);
    expect($computer->refresh()->status)->toBe('offline')
        ->and($secondComputer->refresh()->status)->toBe('offline');

    $this->actingAs($user)->delete(route('club-map.destroy', $zone))
        ->assertRedirect()
        ->assertSessionHas('status');
    $this->assertModelMissing($zone);
    $this->assertModelExists($otherZone);
});

test('club zone input is validated', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('club-map.store'), [
        'name' => '',
        'icon' => 'script-tag',
        'theme' => 'unknown',
        'status' => 'unknown',
        'is_featured' => false,
        'sort_order' => -1,
    ])->assertSessionHasErrors(['name', 'icon', 'theme', 'status', 'sort_order']);

    expect(ClubZone::query()->count())->toBe(0);
});
