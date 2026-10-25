<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DailyStockController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/menu', [LandingController::class, 'menu'])->name('menu');
Route::get('/cart', [LandingController::class, 'cart'])->name('cart.index');
Route::post('/orders', [TransactionController::class, 'customerStore'])->middleware('throttle:10,1')->name('orders.store');
Route::post('/orders/{transaction}/proof', [TransactionController::class, 'uploadPaymentProof'])->middleware(['signed', 'throttle:5,1'])->name('orders.proof');
Route::get('/orders/confirmation', [TransactionController::class, 'customerConfirmation'])->name('orders.confirmation');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');
    Route::get('/verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');
    Route::get('/confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('/confirm-password', [ConfirmablePasswordController::class, 'store']);
});

Route::middleware(['auth', 'role:owner,cashier'])->group(function () {
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/transactions', [TransactionController::class, 'posStore'])->name('pos.transactions.store');
    Route::get('/pos/orders/{transaction}/receipt', [TransactionController::class, 'receipt'])->name('pos.orders.receipt');
    Route::get('/staff/menus', [MenuController::class, 'staffIndex'])->name('staff.menus');
    Route::get('/manage/menus', [MenuController::class, 'manage'])->name('menus.manage');
    Route::post('/manage/menus', [MenuController::class, 'store'])->name('menus.store');
    Route::patch('/manage/menus/{menu}', [MenuController::class, 'update'])->name('menus.update');
    Route::patch('/pos/orders/{transaction}/kitchen', [PosController::class, 'updateKitchenStatus'])->name('pos.orders.kitchen');
    Route::post('/pos/orders/{transaction}/payment', [PosController::class, 'confirmPayment'])->name('pos.orders.payment');
    Route::get('/pos/orders/{transaction}/proof', [TransactionController::class, 'showPaymentProof'])->name('staff.orders.proof');
    Route::get('/stock', [DailyStockController::class, 'index'])->name('stock.index');
    Route::post('/stock', [DailyStockController::class, 'store'])->name('stock.store');
});

Route::middleware(['auth', 'role:owner'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export', [DashboardController::class, 'exportCsv'])->name('dashboard.export');
    Route::get('/dashboard/print', [DashboardController::class, 'printReport'])->name('dashboard.print');
    Route::get('/manage/staff', [StaffController::class, 'index'])->name('staff.manage');
    Route::post('/manage/staff', [StaffController::class, 'store'])->name('staff.store');
    Route::put('/manage/staff/{user}', [StaffController::class, 'update'])->name('staff.update');
    Route::post('/manage/staff/{user}/reset-password', [StaffController::class, 'resetPassword'])->name('staff.reset-password');
    Route::delete('/manage/staff/{user}', [StaffController::class, 'destroy'])->name('staff.destroy');
});

Route::prefix('api')->group(function () {
    Route::get('/menus', [MenuController::class, 'index']);
});

Route::middleware(['auth', 'role:owner,cashier'])->prefix('api')->group(function () {
    Route::post('/menus', [MenuController::class, 'store']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::post('/stocks', [DailyStockController::class, 'store']);
    Route::get('/stocks', [DailyStockController::class, 'index']);
});

Route::middleware(['auth', 'role:owner'])->prefix('api')->group(function () {
    Route::get('/dashboard-summary', [DashboardController::class, 'summary']);
});
