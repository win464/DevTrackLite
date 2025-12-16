<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/status', function () {
    return response()->json(['status' => 'ok']);
});

// API Documentation
Route::get('/docs', function () {
    return view('api.docs');
})->name('api.docs');

Route::get('/openapi.yaml', function () {
    return response()->file(base_path('openapi.yaml'), [
        'Content-Type' => 'application/x-yaml',
    ]);
})->name('api.openapi');

// Public read-only endpoints (30 requests per minute)
use App\Http\Controllers\Api\Admin\ProjectController;
use App\Http\Controllers\Api\MilestoneController;
use App\Http\Controllers\Api\StatsController;

Route::middleware('throttle:30,1')->group(function () {
    // Public stats endpoint
    Route::get('/stats', [StatsController::class, 'publicStats'])->name('api.stats.public');
    
    // Public projects & milestones endpoints
    Route::get('/public/projects', [ProjectController::class, 'index'])->name('api.projects.index');
    Route::get('/public/projects/{project}', [ProjectController::class, 'show'])->name('api.projects.show');
    Route::get('/public/projects/{project}/milestones', [MilestoneController::class, 'index'])->name('api.milestones.index');
});

// Public auth endpoints (issue/revoke tokens)
use App\Http\Controllers\Api\Auth\TokenController;

Route::post('/token', [TokenController::class, 'issue'])->middleware('throttle:10,1');

// Authenticated endpoints (60 requests per minute)
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // User stats endpoint
    Route::get('/stats', [StatsController::class, 'userStats'])->name('api.stats.user');

    // Revoke token
    Route::post('/token/revoke', [TokenController::class, 'revoke']);

    // Milestone write endpoints (owners or admins)
    Route::post('/projects/{project}/milestones', [MilestoneController::class, 'store']);
    Route::patch('/milestones/{milestone}', [MilestoneController::class, 'update'])->middleware('can:update,milestone');
    Route::delete('/milestones/{milestone}', [MilestoneController::class, 'destroy'])->middleware('can:delete,milestone');
});

// Protected admin routes (100 requests per minute)
Route::middleware(['auth:sanctum', 'throttle:100,1', 'App\\Http\\Middleware\\EnsureUserHasRole:admin'])->prefix('admin')->group(function () {
    // Admin stats endpoint
    Route::get('/stats', [StatsController::class, 'userStats'])->name('api.admin.stats');

    // Admin project CRUD
    Route::apiResource('projects', App\Http\Controllers\Api\Admin\ProjectController::class)->names([
        'index' => 'api.admin.projects.index',
        'store' => 'api.admin.projects.store',
        'show' => 'api.admin.projects.show',
        'update' => 'api.admin.projects.update',
        'destroy' => 'api.admin.projects.destroy',
    ]);
});
