<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/debug-db', function () {
    return [
        'default_config_value' => config('database.default'),
        'env_raw_value' => env('DB_CONNECTION'),
        'all_connections' => array_keys(config('database.connections')),
    ];
});

Route::get('/', function () {
    return view('welcome');
});

