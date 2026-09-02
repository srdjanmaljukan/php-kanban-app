<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\Card;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CardTest extends TestCase
{
    use RefreshDatabase;

    private function createBoardWithColumn(): array
    {
        $user = User::factory()->create();
        $board = Board::factory()->create(['owner_id' => $user->id]);
        $board->members()->attach($user->id, ['role' => 'owner']);
        $column = BoardColumn::factory()->create(['board_id' => $board->id]);

        return [$user, $board, $column];
    }

    public function test_first_card_in_empty_column_gets_position_zero(): void
    {
        [$user, $board, $column] = $this->createBoardWithColumn();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/columns/{$column->id}/cards", ['title' => 'First card']);

        $response->assertStatus(201)
            ->assertJsonPath('position', 0);
    }

    public function test_second_card_gets_position_one(): void
    {
        [$user, $board, $column] = $this->createBoardWithColumn();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/columns/{$column->id}/cards", ['title' => 'First card']);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/columns/{$column->id}/cards", ['title' => 'Second card']);

        $response->assertJsonPath('position', 1);
    }

    public function test_moving_card_to_another_column_shifts_positions_correctly(): void
    {
        [$user, $board, $columnA] = $this->createBoardWithColumn();
        $columnB = BoardColumn::factory()->create(['board_id' => $board->id]);

        $card1 = Card::factory()->create(['column_id' => $columnB->id, 'position' => 0]);
        $card2 = Card::factory()->create(['column_id' => $columnB->id, 'position' => 1]);
        $movedCard = Card::factory()->create(['column_id' => $columnA->id, 'position' => 0]);

        // Prebacujemo karticu iz Column A u Column B, na poziciju 1 (između card1 i card2)
        $this->actingAs($user, 'sanctum')
            ->putJson("/api/cards/{$movedCard->id}", [
                'column_id' => $columnB->id,
                'position' => 1,
            ]);

        $this->assertDatabaseHas('cards', ['id' => $card1->id, 'position' => 0]);
        $this->assertDatabaseHas('cards', ['id' => $movedCard->id, 'position' => 1, 'column_id' => $columnB->id]);
        $this->assertDatabaseHas('cards', ['id' => $card2->id, 'position' => 2]);
    }

    public function test_deleting_card_resequences_remaining_positions(): void
    {
        [$user, $board, $column] = $this->createBoardWithColumn();

        $card0 = Card::factory()->create(['column_id' => $column->id, 'position' => 0]);
        $card1 = Card::factory()->create(['column_id' => $column->id, 'position' => 1]);
        $card2 = Card::factory()->create(['column_id' => $column->id, 'position' => 2]);

        $this->actingAs($user, 'sanctum')->deleteJson("/api/cards/{$card1->id}");

        $this->assertDatabaseHas('cards', ['id' => $card0->id, 'position' => 0]);
        $this->assertDatabaseHas('cards', ['id' => $card2->id, 'position' => 1]);
        $this->assertDatabaseMissing('cards', ['id' => $card1->id]);
    }

    public function test_non_member_cannot_add_card_to_column(): void
    {
        [$owner, $board, $column] = $this->createBoardWithColumn();
        $outsider = User::factory()->create();

        $response = $this->actingAs($outsider, 'sanctum')
            ->postJson("/api/columns/{$column->id}/cards", ['title' => 'Sneaky card']);

        $response->assertStatus(403);
    }
}