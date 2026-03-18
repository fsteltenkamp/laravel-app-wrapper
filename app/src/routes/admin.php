<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('admin')->group(function () {
        Route::view('dashboard', 'dashboard')->name('admin.dashboard');
    });
});
