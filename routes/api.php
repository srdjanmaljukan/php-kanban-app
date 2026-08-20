<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\BoardMemberController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::apiResource('boards', BoardController::class);

    Route::post('/boards/{board}/columns', [ColumnController::class, 'store']);
    Route::put('/columns/{column}', [ColumnController::class, 'update']);
    Route::delete('/columns/{column}', [ColumnController::class, 'destroy']);

    Route::post('/columns/{column}/cards', [CardController::class, 'store']);
    Route::put('/cards/{card}', [CardController::class, 'update']);
    Route::delete('/cards/{card}', [CardController::class, 'destroy']);

    Route::post('/boards/{board}/members', [BoardMemberController::class, 'store']);
    Route::delete('/boards/{board}/members/{user}', [BoardMemberController::class, 'destroy']);
});