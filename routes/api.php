<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\StatsController;

Route::get('/tickets', [TicketController::class, 'index']);
Route::post('/tickets', [TicketController::class, 'store']);
Route::get('/tickets/{id}', [TicketController::class, 'show']);
Route::patch('/tickets/{id}', [TicketController::class, 'update']);
Route::post('/tickets/{id}/classify', [TicketController::class, 'classify'])
    ->middleware('throttle:classify');

Route::get('/stats', [StatsController::class, 'index']);