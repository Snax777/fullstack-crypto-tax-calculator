<?php

use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\TransactionUploadController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Services\FIFOCalculatorService;


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
Route::post('/transactions/upload', [TransactionUploadController::class, 'upload']);
// Route::post('/transactions', [TransactionUploadController::class, 'transactions']);

Route::post('/calculate', function (Request $request, FIFOCalculatorService $calculator) {
  $request->validate([
    'session_id' => 'required|string'
  ]);

  try {
    $result = $calculator->calculate($request->session_id);

    return response()->json([
      'status' => 'success',
      'data' => $result
    ]);
  } catch (\Exception $e) {
    return response()->json([
      'status' => 'fail',
      'message' => $e->getMessage()
    ], 400);
  }
});
