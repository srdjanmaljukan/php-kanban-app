<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CardController extends Controller
{
    public function store(Request $request, BoardColumn $column)
    {
        if (!$this->userIsMember($request, $column->board)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $nextPosition = ($column->cards()->max('position') ?? -1) + 1;

        $card = $column->cards()->create([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'position' => $nextPosition,
        ]);

        return response()->json($card, 201);
    }

    public function update(Request $request, Card $card)
    {
        if (!$this->userIsMember($request, $card->column->board)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'column_id' => 'sometimes|required|exists:columns,id',
            'position' => 'sometimes|required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::transaction(function () use ($request, $card) {
            $card = Card::where('id', $card->id)->lockForUpdate()->first();
            $oldColumnId = $card->column_id;
            $newColumnId = $request->input('column_id', $oldColumnId);
            $newPosition = $request->input('position', $card->position);

            if ($oldColumnId != $newColumnId) {
                // Kartica mijenja kolonu:
                // 1) u STAROJ koloni, sklopi rupu koja ostaje iza nje (sve poslije nje pomjeri za -1)
                Card::where('column_id', $oldColumnId)
                    ->where('position', '>', $card->position)
                    ->decrement('position');

                // 2) u NOVOJ koloni, napravi mjesto na novoj poziciji (sve od te pozicije pomjeri za +1)
                Card::where('column_id', $newColumnId)
                    ->where('position', '>=', $newPosition)
                    ->increment('position');
            } elseif ($newPosition != $card->position) {
                // Kartica ostaje u ISTOJ koloni, samo mijenja mjesto unutar nje
                if ($newPosition > $card->position) {
                    // Pomjera se NANIŽE (npr. sa pozicije 0 na 2) — sve između pomjeri za -1
                    Card::where('column_id', $newColumnId)
                        ->where('position', '>', $card->position)
                        ->where('position', '<=', $newPosition)
                        ->decrement('position');
                } else {
                    // Pomjera se NAVIŠE (npr. sa pozicije 2 na 0) — sve između pomjeri za +1
                    Card::where('column_id', $newColumnId)
                        ->where('position', '>=', $newPosition)
                        ->where('position', '<', $card->position)
                        ->increment('position');
                }
            }

            $card->update($request->only(['title', 'description', 'due_date', 'column_id', 'position']));
        });

        return response()->json($card->fresh());
    }

    public function destroy(Request $request, Card $card)
    {
        if (!$this->userIsMember($request, $card->column->board)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        DB::transaction(function () use ($card) {
            $columnId = $card->column_id;
            $deletedPosition = $card->position;

            $card->delete();

            // Popuni rupu — sve kartice ISPOD obrisane pomjeri za jedno mjesto naviše
            Card::where('column_id', $columnId)
                ->where('position', '>', $deletedPosition)
                ->decrement('position');
        });

        return response()->json(['message' => 'Card deleted successfully.']);
    }

    private function userIsMember(Request $request, Board $board): bool
    {
        return $board->members()->where('user_id', $request->user()->id)->exists();
    }
}
