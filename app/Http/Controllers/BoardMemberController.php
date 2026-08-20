<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BoardMemberController extends Controller
{
    // Dodaje korisnika na board preko email adrese (poziv)
    public function store(Request $request, Board $board)
    {
        if ($board->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Only the owner can invite members.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userToInvite = User::where('email', $request->email)->first();

        if ($board->members()->where('user_id', $userToInvite->id)->exists()) {
            return response()->json(['message' => 'This user is already a member of the board.'], 409);
        }

        $board->members()->attach($userToInvite->id, ['role' => 'member']);

        return response()->json([
            'message' => 'Member added successfully.',
            'member' => $userToInvite,
        ], 201);
    }

    // Uklanja člana sa board-a
    public function destroy(Request $request, Board $board, User $user)
    {
        if ($board->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Only the owner can remove members.'], 403);
        }

        if ($user->id === $board->owner_id) {
            return response()->json(['message' => 'The owner cannot be removed from the board.'], 400);
        }

        $board->members()->detach($user->id);

        return response()->json(['message' => 'Member removed successfully.']);
    }
}