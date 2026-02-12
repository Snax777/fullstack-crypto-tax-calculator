<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CsvTransactionImporter;
use Illuminate\Http\Request;

class TransactionUploadController extends Controller
{
    protected $importService;

    public function __construct(CsvTransactionImporter $importService)
    {
        $this->importService = $importService;
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240'
        ]);

        try {
            $file = $request->file('file');
            $tempPath = $file->getRealPath();

            // Auto-detects file type (CSV or Excel) and imports accordingly
            $result = $this->importService->importFromFile($tempPath);

            return response()->json([
                'status' => 'success',
                'message' => "Imported {$result['count']} transactions successfully",
                'data' => [
                    'session_id' => $result['session_id'],
                    'count' => $result['count'],
                    'errors' => $result['errors']
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Failed to import transactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getSessionTransactions($sessionId)
    {
        $transactions = \App\Models\Transaction::forSession($sessionId)->orderedByDate()->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'No transactions found for the given session ID',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'session_id' => $sessionId,
                'count' => $transactions->count(),
                'transactions' => $transactions
            ]
        ]);
    }

    public function deleteSession($sessionId)
    {
        $deleted = \App\Models\Transaction::forSession($sessionId)->delete();

        return response()->json([
            'success' => true,
            'message' => "Deleted {$deleted} transactions",
            'count' => $deleted
        ]);
    }
}