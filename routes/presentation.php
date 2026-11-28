<?php

use App\Presentation\BundleController;
use Illuminate\Support\Facades\Route;

Route::get('/api/presentation/services/{service}/bundle', [BundleController::class, 'show'])
    ->name('presentation.bundle');