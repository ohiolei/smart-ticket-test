<?php

use Illuminate\Support\Facades\Route;

Route::get('/{any}', function () {
    return view('app'); // app.blade.php
})->where('any', '.*');


require __DIR__ . '/api.php';