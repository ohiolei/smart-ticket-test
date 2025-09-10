<?php

use Illuminate\Support\Facades\Route;
require __DIR__ . '/api.php';

Route::get('/{any}', function () {
    return view('app'); // app.blade.php
})->where('any', '.*');


