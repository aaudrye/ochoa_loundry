<?php

use App\Http\Controllers\Admin\OrderController as AdminOrder;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Courier\JobController;
use App\Http\Controllers\Customer\OrderController as CustomerOrder;
use App\Http\Controllers\Owner\AccountController;
use App\Http\Controllers\Owner\DashboardController;
use App\Http\Controllers\Owner\FinanceController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', fn () => redirect()->route(auth()->user()->homeRoute()));

    // ===== PELANGGAN =====
    Route::middleware('role:customer')->prefix('customer')->name('customer.')->group(function () {
        Route::get('/', [CustomerOrder::class, 'home'])->name('home');
        Route::get('/pesan', [CustomerOrder::class, 'create'])->name('orders.create');
        Route::post('/pesan', [CustomerOrder::class, 'store'])->name('orders.store');
        Route::get('/pesanan', [CustomerOrder::class, 'index'])->name('orders.index');
        Route::get('/pesanan/{order}', [CustomerOrder::class, 'show'])->name('orders.show');
        Route::get('/pesanan/{order}/bayar', [CustomerOrder::class, 'payment'])->name('orders.payment');
        Route::post('/pesanan/{order}/bayar', [CustomerOrder::class, 'pay'])->name('orders.pay');
        Route::post('/pesanan/{order}/jadwal', [CustomerOrder::class, 'slot'])->name('orders.slot');
    });

    // ===== ADMIN =====
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminOrder::class, 'incoming'])->name('incoming');
        Route::get('/pesanan/{order}/kurir', [AdminOrder::class, 'assignForm'])->name('assign.form');
        Route::post('/pesanan/{order}/kurir', [AdminOrder::class, 'assign'])->name('assign');
        Route::get('/proses', [AdminOrder::class, 'process'])->name('process');
        Route::post('/pesanan/{order}/maju', [AdminOrder::class, 'advance'])->name('advance');
        Route::post('/pesanan/{order}/jadwal', [AdminOrder::class, 'slot'])->name('slot');
        Route::get('/notifikasi', [AdminOrder::class, 'notifications'])->name('notifications');
        Route::get('/histori', [AdminOrder::class, 'history'])->name('history');
    });

    // ===== KURIR =====
    Route::middleware('role:kurir')->prefix('kurir')->name('courier.')->group(function () {
        Route::get('/', [JobController::class, 'home'])->name('home');
        Route::get('/tugas/{order}', [JobController::class, 'show'])->name('show');
        Route::post('/tugas/{order}/terima', [JobController::class, 'accept'])->name('accept');
        Route::post('/tugas/{order}/sampai', [JobController::class, 'arrive'])->name('arrive');
        Route::post('/tugas/{order}/timbang', [JobController::class, 'weigh'])->name('weigh');
        Route::post('/tugas/{order}/serahkan', [JobController::class, 'deliver'])->name('deliver');
        Route::get('/riwayat', [JobController::class, 'history'])->name('history');
    });

    // ===== OWNER =====
    Route::middleware('role:owner')->prefix('owner')->name('owner.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/keuangan', [FinanceController::class, 'index'])->name('finance');
        Route::post('/keuangan', [FinanceController::class, 'store'])->name('finance.store');
        Route::get('/keuangan/export', [FinanceController::class, 'export'])->name('finance.export');
        Route::get('/akun', [AccountController::class, 'edit'])->name('account');
        Route::put('/akun', [AccountController::class, 'update'])->name('account.update');
        Route::put('/akun/password', [AccountController::class, 'password'])->name('account.password');
    });
});