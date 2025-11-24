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

// Public read-only endpoints (examples)
Route::get('/projects', function () {
    return response()->json(['data' => []]);
});

// Public auth endpoints (issue/revoke tokens)
use App\Http\Controllers\Api\Auth\TokenController;

Route::post('/token', [TokenController::class, 'issue'])->middleware('throttle:10,1');
Route::post('/token/revoke', [TokenController::class, 'revoke'])->middleware('auth:sanctum');

// Protected admin routes
Route::middleware(['auth:sanctum', 'App\\Http\\Middleware\\EnsureUserHasRole:admin'])->prefix('admin')->group(function () {
    // Example admin-only endpoint
    Route::get('/ping', function (Request $request) {
        return response()->json(['admin' => $request->user()->email]);
    });

    // Place admin API resources here, e.g. ProjectController, MilestoneController
    // Route::apiResource('projects', App\Http\Controllers\Api\Admin\ProjectController::class);
});
