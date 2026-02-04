<?php

use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\TransactionUploadController;
use Illuminate\Support\Facades\Route;

Route::apiResource('transactions', TransactionController::class);

Route::post('/transactions/upload', [TransactionUploadController::class, 'upload']);
// Route::post('/transactions', [TransactionUploadController::class, 'transactions']);
