<?php

namespace Tests\Feature;

use App\Models\Courier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourierTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_couriers_sorted_by_name_by_default(): void
    {
        Courier::factory()->create(['name' => 'Zulkifli']);
        Courier::factory()->create(['name' => 'Andi Pratama']);
        Courier::factory()->create(['name' => 'Budi Santoso']);

        $response = $this->getJson('/couriers');

        $response->assertOk()
            ->assertJsonStructure(['data', 'current_page', 'per_page', 'total']);

        $names = collect($response->json('data'))->pluck('name')->all();
        $this->assertSame(['Andi Pratama', 'Budi Santoso', 'Zulkifli'], $names);
    }

    public function test_index_can_override_sort_by_registration_date(): void
    {
        $older = Courier::factory()->create(['name' => 'Andi', 'created_at' => now()->subDays(2)]);
        $newer = Courier::factory()->create(['name' => 'Zaki', 'created_at' => now()]);

        $response = $this->getJson('/couriers?sort_by=created_at&sort_dir=desc');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertSame([$newer->id, $older->id], $ids);
    }

    public function test_index_can_search_multi_word_name(): void
    {
        Courier::factory()->create(['name' => 'Budiono Hadi Agung']);
        Courier::factory()->create(['name' => 'Agung Wijaya']);

        $response = $this->getJson('/couriers?search=budi+agung');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Budiono Hadi Agung');
    }

    public function test_index_can_filter_by_levels(): void
    {
        Courier::factory()->create(['level' => 1]);
        Courier::factory()->create(['level' => 2]);
        Courier::factory()->create(['level' => 3]);
        Courier::factory()->create(['level' => 5]);

        $response = $this->getJson('/couriers?level=2,3');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_show_returns_all_courier_data(): void
    {
        $courier = Courier::factory()->create([
            'code'  => 'KUR-001',
            'name'  => 'Budiono Hadi Agung',
            'level' => 3,
        ]);

        $response = $this->getJson("/couriers/{$courier->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $courier->id)
            ->assertJsonPath('data.code', 'KUR-001')
            ->assertJsonPath('data.name', 'Budiono Hadi Agung')
            ->assertJsonPath('data.level', 3);
    }

    public function test_store_validates_and_saves_courier_to_database(): void
    {
        // Fails validation when level is out of 1-5 range or required fields are missing
        $this->postJson('/couriers', ['level' => 6])
            ->assertStatus(422)
            ->assertJsonPath('status', 'error');

        // Valid payload saves to database
        $payload = [
            'code'      => 'KUR-100',
            'name'      => 'Budiono Hadi Agung',
            'email'     => 'budiono@example.com',
            'phone'     => '081234567890',
            'level'     => 3,
            'is_active' => true,
        ];

        $this->postJson('/couriers', $payload)
            ->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.code', 'KUR-100');

        $this->assertDatabaseHas('couriers', [
            'code'  => 'KUR-100',
            'email' => 'budiono@example.com',
            'level' => 3,
        ]);
    }

    public function test_update_validates_and_updates_courier_in_database(): void
    {
        $courier = Courier::factory()->create([
            'name'  => 'Old Name',
            'level' => 1,
        ]);

        $this->putJson("/couriers/{$courier->id}", [
            'name'  => 'Updated Name',
            'level' => 4,
        ])->assertOk()
          ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('couriers', [
            'id'    => $courier->id,
            'name'  => 'Updated Name',
            'level' => 4,
        ]);
    }

    public function test_destroy_deletes_courier_from_database(): void
    {
        $courier = Courier::factory()->create();

        $this->deleteJson("/couriers/{$courier->id}")
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseMissing('couriers', [
            'id' => $courier->id,
        ]);
    }
}
