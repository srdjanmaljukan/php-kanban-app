<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\BoardColumn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ColumnController extends Controller
{
    public function store(Request $request, Board $board)
    {
        if (!$this->userIsMember($request, $board)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Nova kolona ide na kraj (poslije svih postojećih)
        $nextPosition = $board->columns()->max('position') + 1;

        $column = $board->columns()->create([
            'name' => $request->name,
            'position' => $nextPosition,
        ]);

        return response()->json($column, 201);
    }

    public function update(Request $request, BoardColumn $column)
    {
        if (!$this->userIsMember($request, $column->board)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'position' => 'sometimes|required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $column->update($request->only(['name', 'position']));

        return response()->json($column);
    }

    public function destroy(Request $request, BoardColumn $column)
    {
        if (!$this->userIsMember($request, $column->board)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $column->delete();

        return response()->json(['message' => 'Column deleted successfully.']);
    }

    private function userIsMember(Request $request, Board $board): bool
    {
        return $board->members()->where('user_id', $request->user()->id)->exists();
    }
}