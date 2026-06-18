<?php

use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PetController;
use Illuminate\Support\Facades\Route;

// Auth (public)
Route::prefix('auth')->group(function () {
    Route::post('register',   [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::post('login',      [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:10,1');
    Route::post('resend-otp', [AuthController::class, 'resendOtp'])->middleware('throttle:3,5');

    Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Pets
    Route::post('pets/{id}/analyses', [AnalysisController::class, 'reanalyze']);
    Route::apiResource('pets', PetController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

    // Analyses
    Route::get('analyses',         [AnalysisController::class, 'index']);
    Route::post('analyses',        [AnalysisController::class, 'store']);
    Route::delete('analyses/{id}', [AnalysisController::class, 'destroy']);
});
