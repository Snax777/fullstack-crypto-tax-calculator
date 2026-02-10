<?php

use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\TransactionUploadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Upload endpoint - MUST come before other transaction routes
Route::post('transactions/upload', [TransactionUploadController::class, 'upload']);

// Tax year grouping routes - MUST come before apiResource
Route::get('transactions/tax-year/grouping', [TransactionController::class, 'getTaxYearGrouping']);
Route::get('transactions/tax-year/simple', [TransactionController::class, 'getTaxYearGroupingSimple']);

// Session management routes (if you need them)
Route::get('transactions/session/{sessionId}', [TransactionUploadController::class, 'getSessionTransactions']);
Route::delete('transactions/session/{sessionId}', [TransactionUploadController::class, 'deleteSession']);

// Standard CRUD routes - MUST come LAST
Route::apiResource('transactions', TransactionController::class);