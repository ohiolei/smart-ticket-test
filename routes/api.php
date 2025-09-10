<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\StatsController;


Route::get('/api/tickets/status-summary', [TicketController::class, 'statusSummary']);
Route::get('/api/tickets/category-summary', [TicketController::class, 'categorySummary']);
Route::get('api/tickets/stats', [TicketController::class, 'stats']);
Route::get('/api/stats', [StatsController::class, 'index']);

Route::get('/api/tickets', [TicketController::class, 'index']);
Route::post('/api/tickets', [TicketController::class, 'store']);
Route::get('/api/tickets/{id}', [TicketController::class, 'show']);
Route::patch('/api/tickets/{id}', [TicketController::class, 'update']);
Route::post('/api/tickets/{id}/classify', [TicketController::class, 'classify']);
     //  ->middleware('throttle:classify');

