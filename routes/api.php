<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('/wallet', WalletController::class)->only(['index', 'show']);
Route::post('/wallet/{wallet}/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');

Route::apiResource('/transfer', TransferController::class);

