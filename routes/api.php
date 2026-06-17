<?php

use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PetController;
use Illuminate\Support\Facades\Route;

// Auth (public)
Route::prefix('auth')->group(function () {
    Route::post('register',   [AuthController::class, 'register']);
    Route::post('login',      [AuthController::class, 'login']);
    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('resend-otp', [AuthController::class, 'resendOtp']);

    Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Pets
    Route::apiResource('pets', PetController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

    // Analyses
    Route::get('analyses',         [AnalysisController::class, 'index']);
    Route::post('analyses',        [AnalysisController::class, 'store']);
    Route::delete('analyses/{id}', [AnalysisController::class, 'destroy']);
});
