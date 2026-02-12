<?php

use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\TransactionUploadController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WalletTransactionController;
use App\Http\Controllers\TaxReportController;

Route::apiResource('transactions', TransactionController::class);

Route::post('/transactions/upload', [TransactionUploadController::class, 'upload']);
// Route::post('/transactions', [TransactionUploadController::class, 'transactions']);


Route::apiResource('wallets', WalletController::class);
Route::apiResource('transactions', WalletTransactionController::class);

// Tax report routes
Route::prefix('tax')->group(function () {
    Route::get('/wallet/{wallet}/{year?}', [TaxReportController::class, 'calculateWalletTax']);
    Route::get('/wallet/{wallet}/pdf/{year?}', [TaxReportController::class, 'generateWalletPdf']);
});
