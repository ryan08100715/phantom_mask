<?php

use App\Http\Controllers\MaskController;
use App\Http\Controllers\PharmacyController;

Route::get('/pharmacies', [PharmacyController::class, 'index']);
Route::get('/pharmacies/{pharmacy}/masks', [MaskController::class, 'getPharmacyMasks']);
Route::post('/pharmacies/{pharmacy}/masks/batch', [MaskController::class, 'upsertPharmacyMasks']);

Route::patch('/masks/{mask}', [MaskController::class, 'update']);
