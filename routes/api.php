<?php

use App\Http\Controllers\MaskController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\UserController;

Route::get('/pharmacies', [PharmacyController::class, 'index']);
Route::get('/pharmacies/search', [PharmacyController::class, 'search']);
Route::get('/pharmacies/{pharmacy}/masks', [MaskController::class, 'getPharmacyMasks']);
Route::post('/pharmacies/{pharmacy}/masks/batch', [MaskController::class, 'upsertPharmacyMasks']);

Route::get('/masks/search', [MaskController::class, 'search']);
Route::patch('/masks/{mask}', [MaskController::class, 'update']);

Route::get('/users/top-spenders', [UserController::class, 'getTopSpenders']);
Route::post('/users/{user}/purchases', [UserController::class, 'purchase']);
