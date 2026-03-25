<?php

use App\Http\Controllers\Admin\ResourceController as AdminResourceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResourceController as UserResourceController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (request()->user()) {
        return redirect()->route('dashboard');
    }

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => app()->version(),
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    
    // Standard User Dashboard
    Route::get('/dashboard', function () {
        // Redirect admins straight to the resource manager
        if (request()->user() && request()->user()->is_admin) {
            return redirect()->route('admin.resources');
        }
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // ----------------------------------------------------
    // USER END: Standard users can view and download here
    // ----------------------------------------------------
    Route::get('/resources', [UserResourceController::class, 'index'])->name('resources.index');
    Route::post('/resources/folders/{folder}/open', [UserResourceController::class, 'openFolder'])->name('resources.folders.open');
    Route::get('/resources/download/{file}', [UserResourceController::class, 'download'])->name('resources.download');
    
    // PREVIEW ROUTE
    Route::get('/resources/preview/{file}', [UserResourceController::class, 'preview'])->name('resources.preview');


    // ----------------------------------------------------
    // ADMIN END: Only admins can manage files and folders
    // ----------------------------------------------------
    Route::middleware(['admin'])->prefix('admin')->group(function () {
        
        // Main Explorer View & New Analytics Dashboard
        Route::get('/resources', [AdminResourceController::class, 'index'])->name('admin.resources');
        Route::get('/analytics', [AdminResourceController::class, 'analytics'])->name('admin.analytics'); // NEW!
        Route::get('/resources/analytics', [AdminResourceController::class, 'analytics'])->name('admin.resources.analytics');
        Route::delete('/resources', function () {
            return redirect()->route('admin.analytics');
        });
        Route::get('/users', [AdminResourceController::class, 'users'])->name('admin.users');
        
        // Folder Management
        Route::post('/folders', [AdminResourceController::class, 'storeFolder'])->name('admin.folders.store');
        Route::delete('/folders/{folder}', [AdminResourceController::class, 'destroyFolder'])->name('admin.folders.destroy');
        Route::patch('/folders/{folder}/toggle-lock', [AdminResourceController::class, 'toggleFolderLock'])->name('admin.folders.toggle-lock');
        
        // File Management
        Route::post('/files', [AdminResourceController::class, 'storeFile'])->name('admin.files.store');
        Route::delete('/files/{file}', [AdminResourceController::class, 'destroyFile'])->name('admin.files.destroy');
        Route::patch('/files/{file}/toggle-lock', [AdminResourceController::class, 'toggleFileLock'])->name('admin.files.toggle-lock');

        // Portal Content Management (NEW!)
        Route::post('/carousel', [AdminResourceController::class, 'storeCarousel'])->name('admin.carousel.store');
        Route::patch('/carousel/{carousel}', [AdminResourceController::class, 'updateCarousel'])->name('admin.carousel.update');
        Route::delete('/carousel/{carousel}', [AdminResourceController::class, 'destroyCarousel'])->name('admin.carousel.destroy');
        Route::post('/videos', [AdminResourceController::class, 'storeVideo'])->name('admin.videos.store');
        Route::patch('/videos/{video}', [AdminResourceController::class, 'updateVideo'])->name('admin.videos.update');
        Route::delete('/videos/{video}', [AdminResourceController::class, 'destroyVideo'])->name('admin.videos.destroy');

        // User Management
        Route::post('/users', [AdminResourceController::class, 'storeUser'])->name('admin.users.store');
        Route::patch('/users/{user}', [AdminResourceController::class, 'updateUser'])->name('admin.users.update');
        Route::patch('/users/{user}/password', [AdminResourceController::class, 'updateUserPassword'])->name('admin.users.password');
        Route::delete('/users/{user}', [AdminResourceController::class, 'destroyUser'])->name('admin.users.destroy');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
