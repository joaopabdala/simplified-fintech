<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\TransferStoreController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WalletDepositController;
use App\Http\Middleware\IdempotencyKeysMiddleware;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::apiResource('/wallet', WalletController::class)->only(['index', 'show']);

Route::get('/transfer', [TransferController::class, 'index']);
Route::get('/transfer/{transfer}', [TransferController::class, 'show']);

Route::middleware(IdempotencyKeysMiddleware::class)->group(function () {
    Route::post('/transfer', TransferStoreController::class);
    Route::post('/wallet/{wallet}/deposit', WalletDepositController::class)->name('wallet.deposit');
});
