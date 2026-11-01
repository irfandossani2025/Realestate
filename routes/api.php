<?php

use App\Http\Controllers\Api\PublishedPropertyController;
use Illuminate\Support\Facades\Route;

Route::get('/v1/properties', [PublishedPropertyController::class, 'index'])->name('api.properties.index');
