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

// Public read-only endpoints
use App\Http\Controllers\Api\Admin\ProjectController;
use App\Http\Controllers\Api\MilestoneController;

// Public projects & milestones endpoints
Route::get('/public/projects', [ProjectController::class, 'index'])->name('api.projects.index');
Route::get('/public/projects/{project}', [ProjectController::class, 'show'])->name('api.projects.show');
Route::get('/public/projects/{project}/milestones', [MilestoneController::class, 'index'])->name('api.milestones.index');

// Authenticated project/milestone actions for owners (using policy)
Route::middleware('auth:sanctum')->group(function () {
    // Milestone write endpoints (owners or admins)
    Route::post('/projects/{project}/milestones', [MilestoneController::class, 'store']);
    Route::patch('/milestones/{milestone}', [MilestoneController::class, 'update'])->middleware('can:update,milestone');
    Route::delete('/milestones/{milestone}', [MilestoneController::class, 'destroy'])->middleware('can:delete,milestone');
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
    Route::apiResource('projects', App\Http\Controllers\Api\Admin\ProjectController::class)->names([
        'index' => 'api.admin.projects.index',
        'store' => 'api.admin.projects.store',
        'show' => 'api.admin.projects.show',
        'update' => 'api.admin.projects.update',
        'destroy' => 'api.admin.projects.destroy',
    ]);
});
