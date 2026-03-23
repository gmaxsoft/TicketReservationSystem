<?php

use App\Http\Controllers\Admin\ScannerFulfillController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Payment\Przelewy24WebhookController;
use App\Http\Controllers\StaffAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('filament.admin.pages.dashboard');
    }

    return redirect()->route('filament.admin.auth.login');
});

Route::get('/wydarzenia/{event}', [EventController::class, 'show'])->name('events.show');

Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');

Route::post('/platnosc/przelewy24', [PaymentController::class, 'init'])->name('payment.init');

Route::get('/platnosc/zwrot/{token}', function (string $token) {
    return view('payment.return', ['token' => $token]);
})->name('payment.return');

Route::post('/webhooks/przelewy24', Przelewy24WebhookController::class)->name('webhooks.przelewy24');

Route::get('/koncert/logowanie', [StaffAuthController::class, 'show'])->name('concert.staff.login');
Route::post('/koncert/logowanie', [StaffAuthController::class, 'login']);
Route::post('/koncert/wyloguj', [StaffAuthController::class, 'logout'])->name('concert.staff.logout');

Route::middleware('concert.staff')->group(function () {
    Route::get('/koncert/wejscie', [CheckInController::class, 'index'])->name('concert.check-in');
    Route::post('/koncert/wejscie', [CheckInController::class, 'process']);
});

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::post('/admin/scanner/fulfill', ScannerFulfillController::class)->name('admin.scanner.fulfill');
});
