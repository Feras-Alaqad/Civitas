<?php

use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\CitizenController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::redirect('/dashboard', '/admin/dashboard')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin Dashboard
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::get('/profile', function () {
            return view('admin.profile', ['user' => auth()->user()]);
        })->name('profile');
        Route::patch('/profile', [ProfileController::class, 'adminUpdate'])->name('profile.update');

        Route::get('/citizens', [CitizenController::class, 'index'])->name('citizens');

        Route::get('/service/create', [ServiceController::class, 'create'])->name('service.create');
        Route::post('/service/store', [ServiceController::class, 'store'])->name('service.store');

        Route::post('/service/paypal/create-order', [ServiceController::class, 'paypalCreateOrder'])->name('service.paypal-create-order');
        Route::post('/service/paypal/capture-order', [ServiceController::class, 'paypalCaptureOrder'])->name('service.paypal-capture-order');
        Route::get('/service/paypal/return', [ServiceController::class, 'paypalReturn'])->name('service.paypal-return');
        Route::get('/service/paypal/cancel', [ServiceController::class, 'paypalCancel'])->name('service.paypal-cancel');

        Route::post('/import/persons/upload', [ImportController::class, 'upload'])->name('import.persons.upload');
        Route::get('/import/progress/{importId}', [ImportController::class, 'progress'])->name('import.progress');

        Route::get('/audit-logs', [AuditController::class, 'index'])->name('audit-logs');
        Route::get('/audit-logs/audit-trail/{referenceId}', [AuditController::class, 'auditTrail'])->name('audit-logs.audit-trail');
        Route::get('/audit-logs/export', [AuditController::class, 'export'])->name('audit-logs.export');
    });
});

require __DIR__.'/auth.php';
