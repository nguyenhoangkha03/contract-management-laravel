<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContractApiController;
use App\Http\Controllers\Api\AnalyticsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API is working',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
});

// Client Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']); // Tạm thời bỏ middleware
    Route::get('/user', [AuthController::class, 'user']); // Tạm thời bỏ middleware
});

// Contract API routes for customer portal
Route::prefix('contracts')->group(function () {
    // Test endpoint
    Route::get('/test', [ContractApiController::class, 'test']);
    
    // Get contracts by client email
    Route::get('/by-email', [ContractApiController::class, 'getContractsByEmail']);
    
    // Get contract details by ID (with email verification)
    Route::get('/{id}/detail', [ContractApiController::class, 'getContractDetail']);
    
    // Get dashboard statistics
    Route::get('/dashboard-stats', [ContractApiController::class, 'getDashboardStats']);
});

// Optional: Add CORS middleware for API routes
Route::middleware(['api'])->group(function () {
    // Additional API routes can be added here
});

// Analytics API routes
Route::prefix('analytics')->middleware(['auth:api'])->group(function () {
    // Overview and dashboard
    Route::get('/overview', [AnalyticsController::class, 'overview']);
    Route::get('/performance-dashboard', [AnalyticsController::class, 'performanceDashboard']);
    Route::get('/trends', [AnalyticsController::class, 'trends']);
    Route::get('/risk-analysis', [AnalyticsController::class, 'riskAnalysis']);
    
    // Contract specific analytics
    Route::get('/contracts/{contractId}', [AnalyticsController::class, 'contractAnalytics']);
    Route::post('/contracts/{contractId}/update', [AnalyticsController::class, 'updateContractAnalytics']);
    
    // Revenue forecasting
    Route::get('/revenue-forecast', [AnalyticsController::class, 'revenueForecast']);
    Route::post('/revenue-forecast/generate', [AnalyticsController::class, 'generateForecast']);
    
    // Export
    Route::get('/export', [AnalyticsController::class, 'export']);
});

// Optional: Rate limiting for API
Route::middleware(['throttle:api'])->group(function () {
    // Rate-limited routes
});