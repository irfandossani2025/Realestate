<?php

use App\Http\Controllers\Api\PublishedPropertyController;
use Illuminate\Support\Facades\Route;

Route::get('/v1/properties', [PublishedPropertyController::class, 'index'])->middleware('throttle:60,1')->name('api.properties.index');
