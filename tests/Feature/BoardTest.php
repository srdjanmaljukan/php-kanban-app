<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_board(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/boards', ['name' => 'My Project']);

        $response->assertStatus(201)
            ->assertJsonPath('name', 'My Project');

        $this->assertDatabaseHas('boards', [
            'name' => 'My Project',
            'owner_id' => $user->id,
        ]);
    }

    public function test_creating_a_board_generates_three_default_columns(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/boards', ['name' => 'My Project']);

        $response->assertJsonCount(3, 'columns');
    }

    public function test_board_creator_becomes_owner_member(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/boards', ['name' => 'My Project']);

        $this->assertDatabaseHas('board_members', [
            'user_id' => $user->id,
            'role' => 'owner',
        ]);
    }

    public function test_user_can_only_see_boards_they_are_a_member_of(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $boardA = Board::factory()->create(['owner_id' => $userA->id]);
        $boardA->members()->attach($userA->id, ['role' => 'owner']);

        $boardB = Board::factory()->create(['owner_id' => $userB->id]);
        $boardB->members()->attach($userB->id, ['role' => 'owner']);

        $response = $this->actingAs($userA, 'sanctum')->getJson('/api/boards');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $boardA->id);
    }

    public function test_non_member_cannot_view_board_details(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();

        $board = Board::factory()->create(['owner_id' => $owner->id]);
        $board->members()->attach($owner->id, ['role' => 'owner']);

        $response = $this->actingAs($outsider, 'sanctum')
            ->getJson("/api/boards/{$board->id}");

        $response->assertStatus(403);
    }

    public function test_only_owner_can_update_board(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $board = Board::factory()->create(['owner_id' => $owner->id]);
        $board->members()->attach($owner->id, ['role' => 'owner']);
        $board->members()->attach($member->id, ['role' => 'member']);

        $response = $this->actingAs($member, 'sanctum')
            ->putJson("/api/boards/{$board->id}", ['name' => 'Renamed']);

        $response->assertStatus(403);
    }

    public function test_only_owner_can_delete_board(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $board = Board::factory()->create(['owner_id' => $owner->id]);
        $board->members()->attach($owner->id, ['role' => 'owner']);
        $board->members()->attach($member->id, ['role' => 'member']);

        $response = $this->actingAs($member, 'sanctum')
            ->deleteJson("/api/boards/{$board->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('boards', ['id' => $board->id]);
    }

    public function test_unauthenticated_user_cannot_create_board(): void
    {
        $response = $this->postJson('/api/boards', ['name' => 'My Project']);

        $response->assertStatus(401);
    }
}