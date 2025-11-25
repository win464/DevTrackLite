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
use App\Http\Controllers\Api\Admin\ProjectController;

// Public projects index
Route::get('/projects', [ProjectController::class, 'index']);

// milestones public listing
use App\Http\Controllers\Api\MilestoneController;
Route::get('/projects/{project}/milestones', [MilestoneController::class, 'index']);

// Public project detail
Route::get('/projects/{project}', [App\Http\Controllers\Api\Admin\ProjectController::class, 'show']);

// Authenticated project actions for owners (create/update/delete) using policy
Route::middleware('auth:sanctum')->group(function () {
    // Any authenticated user can create a project (policy enforces this in controller if desired)
    Route::post('/projects', [ProjectController::class, 'store']);

    // Owners (or admins) can update/delete their projects — enforced via policy middleware
    Route::patch('/projects/{project}', [ProjectController::class, 'update'])->middleware('can:update,project');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->middleware('can:delete,project');

    // Milestone write endpoints (owners or admins)
    Route::post('/projects/{project}/milestones', [MilestoneController::class, 'store']);
    Route::patch('/milestones/{milestone}', [MilestoneController::class, 'update'])->middleware('can:update,project');
    Route::delete('/milestones/{milestone}', [MilestoneController::class, 'destroy'])->middleware('can:delete,project');
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

    // Ability-protected endpoint for token ability testing
    Route::get('/ability-ping', function (Request $request) {
        return response()->json(['ok' => true]);
    })->middleware('ability:admin:ping');

    // Place admin API resources here, e.g. ProjectController, MilestoneController
    Route::apiResource('projects', App\Http\Controllers\Api\Admin\ProjectController::class);
});
