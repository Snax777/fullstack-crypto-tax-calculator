<?php

namespace App\Http\Controllers\Api;

use App\Helpers\TaxYearHelper;
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

    /**
     * Get transactions grouped by South African tax year
     * SA Tax Year: 1 March to 28/29 February
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTaxYearGrouping(Request $request)
    {
        $sessionId = $request->query('session_id');

        $query = Transaction::orderedByDate();

        if ($sessionId) {
            $query->forSession($sessionId);
        }

        $transactions = $query->get();

        // Group transactions by tax year
        $grouped = TaxYearHelper::groupByTaxYear($transactions);

        // Format response
        $response = [];
        foreach ($grouped as $taxYear => $yearTransactions) {
            $response[] = [
                'tax_year' => $taxYear,
                'period' => TaxYearHelper::getTaxYearPeriod($taxYear),
                'transactions' => $yearTransactions,
                'count' => count($yearTransactions),
                'summary' => TaxYearHelper::getTaxYearSummary($yearTransactions),
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $response,
            'total_tax_years' => count($response),
        ]);
    }

    /**
     * Alternative: Get transactions grouped by tax year (simple format)
     * Returns just the grouped structure without extra metadata
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTaxYearGroupingSimple(Request $request)
    {
        $sessionId = $request->query('session_id');

        $query = Transaction::orderedByDate();

        if ($sessionId) {
            $query->forSession($sessionId);
        }

        $transactions = $query->get();

        // Group transactions by tax year
        $grouped = TaxYearHelper::groupByTaxYear($transactions);

        // Simple format
        $response = [];
        foreach ($grouped as $taxYear => $yearTransactions) {
            $response[] = [
                'tax_year' => $taxYear,
                'transactions' => $yearTransactions,
                'count' => count($yearTransactions),
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $response,
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