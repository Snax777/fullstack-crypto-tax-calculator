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

	public function getTransactionsByTaxYear(Request $request)
	{
		$sessionId = $request->query('session_id');

		if (!$sessionId) {
			return response()->json([
				'status' => 'fail',
				'message' => 'session_id query parameter is required'
			], 400);
		}

		$query = Transaction::orderedByDate()->forSession($sessionId);
		$transactions = $query->get();

		if ($transactions->isEmpty()) {
			return response()->json([
				'status' => 'fail',
				'message' => 'No transactions found for session: ' . $sessionId
			], 404);
		}

		$grouped = TaxYearHelper::groupByTaxYear($transactions);

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
			'note' => 'This endpoint shows raw transactions only. Use POST /calculate for FIFO gains and tax calculations.'
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
