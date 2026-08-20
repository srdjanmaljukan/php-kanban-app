<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

        $nextPosition = $column->cards()->max('position') + 1;

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

        $card->update($request->only(['title', 'description', 'due_date', 'column_id', 'position']));

        return response()->json($card);
    }

    public function destroy(Request $request, Card $card)
    {
        if (!$this->userIsMember($request, $card->column->board)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $card->delete();

        return response()->json(['message' => 'Card deleted successfully.']);
    }

    private function userIsMember(Request $request, Board $board): bool
    {
        return $board->members()->where('user_id', $request->user()->id)->exists();
    }
}