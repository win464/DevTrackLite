<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', App\Http\Controllers\Web\DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Projects web routes - explicitly named to avoid conflicts with API routes
    Route::resource('projects', ProjectController::class)->names([
        'index' => 'projects.index',
        'create' => 'projects.create',
        'store' => 'projects.store',
        'show' => 'projects.show',
        'edit' => 'projects.edit',
        'update' => 'projects.update',
        'destroy' => 'projects.destroy',
    ]);
    
    // Milestones web routes (nested under projects)
    Route::post('projects/{project}/milestones', [App\Http\Controllers\Web\MilestoneController::class, 'store'])->name('projects.milestones.store');
    Route::put('projects/{project}/milestones/{milestone}', [App\Http\Controllers\Web\MilestoneController::class, 'update'])->name('projects.milestones.update');
    Route::delete('projects/{project}/milestones/{milestone}', [App\Http\Controllers\Web\MilestoneController::class, 'destroy'])->name('projects.milestones.destroy');
});

require __DIR__.'/auth.php';
