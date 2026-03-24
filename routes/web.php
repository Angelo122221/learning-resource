<?php

use App\Http\Controllers\Admin\ResourceController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::get('/dashboard', function () {
    if (request()->user() && request()->user()->is_admin) return redirect()->route('admin.resources');
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::middleware(['admin'])->prefix('admin')->group(function () {
        Route::get('/resources', [ResourceController::class, 'index'])->name('admin.resources');
        
        // Manual paths to ensure no ID mismatch
        Route::post('/folders', [ResourceController::class, 'storeFolder'])->name('admin.folders.store');
        Route::delete('/folders/{folder}', [ResourceController::class, 'destroyFolder'])->name('admin.folders.destroy');
        
        Route::post('/files', [ResourceController::class, 'storeFile'])->name('admin.files.store');
        Route::delete('/files/{file}', [ResourceController::class, 'destroyFile'])->name('admin.files.destroy');
        Route::patch('/files/{file}/toggle-lock', [ResourceController::class, 'toggleFileLock'])->name('admin.files.toggle-lock');
    });
});

require __DIR__.'/auth.php';