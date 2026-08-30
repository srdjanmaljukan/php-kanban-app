<?php

namespace App\Http\Controllers;

use App\Models\Board;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BoardController extends Controller
{
    // Vraća sve board-ove na kojima je ulogovani korisnik član (ili vlasnik)
    public function index(Request $request)
    {
        $boards = $request->user()->boards;

        return response()->json($boards);
    }

    // Kreira novi board; kreator automatski postaje vlasnik i član
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $board = Board::create([
            'name' => $request->name,
            'owner_id' => $request->user()->id,
        ]);

        // Vlasnik se odmah dodaje i kao član (potrebno za members() relaciju da ga vrati)
        $board->members()->attach($request->user()->id, ['role' => 'owner']);

        // Kreiramo tri standardne kolone odmah, da board nije prazan pri kreiranju
        $board->columns()->createMany([
            ['name' => 'To Do', 'position' => 0],
            ['name' => 'In Progress', 'position' => 1],
            ['name' => 'Done', 'position' => 2],
        ]);

        $board->load('columns.cards');
        $board->pivot = (object) ['role' => 'owner'];

        return response()->json($board, 201);
    }

    // Vraća jedan board sa kolonama i karticama, samo ako je korisnik član
    public function show(Request $request, Board $board)
    {
        if (!$this->userIsMember($request, $board)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json($board->load('columns.cards', 'members'));
    }

    // Izmjena imena board-a, samo vlasnik smije
    public function update(Request $request, Board $board)
    {
        if ($board->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Only the owner can edit this board.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $board->update(['name' => $request->name]);

        return response()->json($board);
    }

    // Brisanje board-a, samo vlasnik smije
    public function destroy(Request $request, Board $board)
    {
        if ($board->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Only the owner can delete this board.'], 403);
        }

        $board->delete();

        return response()->json(['message' => 'Board deleted successfully.']);
    }

    // Pomoćna metoda: provjerava da li je ulogovani korisnik član datog board-a
    private function userIsMember(Request $request, Board $board): bool
    {
        return $board->members()->where('user_id', $request->user()->id)->exists();
    }
}
