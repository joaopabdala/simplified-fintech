<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\TransferStoreController;
use App\Http\Controllers\WalletController;
use App\Http\Middleware\IdempotencyKeysMiddleware;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::apiResource('/wallet', WalletController::class)->only(['index', 'show']);
Route::post('/wallet/{wallet}/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');

Route::get('/transfer', [TransferController::class, 'index']);
Route::get('/transfer/{transfer}', [TransferController::class, 'show']);

Route::post('/transfer', TransferStoreController::class)
    ->middleware(IdempotencyKeysMiddleware::class);
