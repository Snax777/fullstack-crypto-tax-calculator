<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionUploadController;

Route::post('/upload-transactions', [TransactionUploadController::class, 'upload']);
Route::post('/transactions', [TransactionUploadController::class, 'transactions']);