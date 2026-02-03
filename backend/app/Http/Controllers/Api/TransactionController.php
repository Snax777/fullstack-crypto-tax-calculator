<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $sessionId = $request->query('session_id');

        $query = Transaction::orderedByDate();

        if ($sessionId) {
            $query->forSession($sessionId);
        }

        $transactions = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $transactions,
            'count' => $transactions->count()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(Transaction::rules());

        if (!isset($validated['session_id'])) {
            $validated['session_id'] = 'session-' . Str::uuid();
        }

        $transaction = Transaction::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction created successfully',
            'data' => $transaction
        ], 201);
    }

    public function show($id)
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Transaction not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $transaction
        ]);
    }

    public function update(Request $request, $id)
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Transaction not found'
            ], 404);
        }

        $validated = $request->validate(Transaction::rules());
        $transaction->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction updated successfully',
            'data' => $transaction
        ]);
    }

    public function destroy($id)
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Transaction not found'
            ], 404);
        }

        $transaction->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction deleted successfully'
        ]);
    }
}
