<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\PlanningController;
use Inertia\Inertia;

// L'accueil et le tableau de bord du kit mènent au planning, cœur du produit.
Route::redirect('/', '/planning')->name('home');

Route::get('planning', PlanningController::class)->name('planning');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::post('bookings', [BookingController::class, 'store'])->middleware('throttle:10,1')->name('bookings.store');
    Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::get('bookings/{booking}/checkout', [BookingController::class, 'checkout'])->name('bookings.checkout');
    Route::post('bookings/{booking}/pay', [BookingController::class, 'pay'])->name('bookings.pay');
    Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::get('kiosk', [KioskController::class, 'show'])->name('kiosk');
    Route::post('kiosk', [KioskController::class, 'validateToken'])->middleware('throttle:30,1')->name('kiosk.validate');
    Route::get('admin', [AdminController::class, 'index'])->name('admin');
    Route::post('admin/users/{user}/suspend', [AdminController::class, 'suspend'])->name('admin.users.suspend');
    Route::post('admin/users/{user}/reactivate', [AdminController::class, 'reactivate'])->name('admin.users.reactivate');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('dashboard', '/planning')->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
