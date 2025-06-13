<?php

use App\Http\Controllers\PharmacyController;

Route::get('/pharmacies', [PharmacyController::class, 'index']);
