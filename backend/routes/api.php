<?php

use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\TransactionUploadController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WalletTransactionController;
// use App\Http\Controllers\TaxReportController;
use App\Http\Controllers\TaxController;

Route::apiResource('transactions', TransactionController::class);

Route::post('/transactions/upload', [TransactionUploadController::class, 'upload']);
// Route::post('/transactions', [TransactionUploadController::class, 'transactions']);


Route::apiResource('wallets', WalletController::class);
Route::get('wallets/{wallet}/transactions', [WalletTransactionController::class, 'index']);

Route::post('tax/calculate', [TaxController::class, 'calculate']);
Route::post('tax/export-pdf', [TaxController::class, 'exportPdf']);
Route::post('tax/preview', [TaxController::class, 'preview']);
