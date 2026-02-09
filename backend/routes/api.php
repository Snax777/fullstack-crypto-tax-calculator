<?php

use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\TransactionUploadController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Services\FIFOCalculatorService;


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
