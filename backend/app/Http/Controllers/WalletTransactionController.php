<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    /**
     * GET /api/transactions
     * List all transactions (with optional filters)
     */
    public function index(Request $request): JsonResponse
    {
        $query = WalletTransaction::with('wallet');

        // Filter by wallet
        if ($request->has('wallet_id')) {
            $query->where('wallet_id', $request->wallet_id);
        }

        // Filter by year
        if ($request->has('year')) {
            $query->whereYear('transaction_date', $request->year);
        }

        // Filter by type (buy/sell/transfer)
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Date range filter
        if ($request->has('from_date')) {
            $query->whereDate('transaction_date', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('transaction_date', '<=', $request->to_date);
        }

        // Sorting
        $orderBy = $request->get('order_by', 'transaction_date');
        $orderDir = $request->get('order_dir', 'desc');
        $query->orderBy($orderBy, $orderDir);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $transactions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $transactions,
            'message' => 'Transactions retrieved successfully'
        ]);
    }

    /**
     * GET /api/transactions/{id}
     * Get single transaction with details
     */
    public function show(WalletTransaction $transaction): JsonResponse
    {
        // Load relationships
        $transaction->load('wallet');

        // Add calculated fields for React frontend
        $data = $transaction->toArray();
        $data['type'] = $transaction->type;
        $data['cryptocurrency_amount'] = $transaction->amount;
        $data['usd_value_at_open'] = $transaction->price_at_open;
        $data['usd_value_at_close'] = $transaction->price_at_close;
        $data['zar_value_base_cost'] = $transaction->zar_value_base_cost;
        $data['zar_value_earning'] = $transaction->zar_value_earning;
        $data['zar_value_capital_gain_loss'] = $transaction->zar_value_capital_gain_loss;

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Transaction retrieved successfully'
        ]);
    }

    /**
     * GET /api/transactions/export
     * Export transactions to CSV (for SARS)
     */
    public function export(Request $request): JsonResponse
    {
        $walletId = $request->get('wallet_id');
        $year = $request->get('year', date('Y'));

        $query = WalletTransaction::with('wallet');

        if ($walletId) {
            $query->where('wallet_id', $walletId);
        }

        $query->whereYear('transaction_date', $year);

        $transactions = $query->get();

        $csvData = $transactions->map(function ($tx) {
            return [
                'Date' => $tx->transaction_date->format('Y-m-d'),
                'Wallet' => $tx->wallet->name,
                'Type' => ucfirst($tx->type),
                'Amount' => $tx->amount,
                'Crypto' => $tx->wallet->crypto_type,
                'Open Price (USD)' => $tx->price_at_open ?? 'N/A',
                'Close Price (USD)' => $tx->price_at_close ?? 'N/A',
                'Base Cost (ZAR)' => number_format($tx->zar_value_base_cost, 2),
                'Proceeds/Earnings (ZAR)' => $tx->type === 'sell' ? number_format($tx->zar_value_earning, 2) : 'N/A',
                'Gain/Loss (ZAR)' => $tx->type === 'sell' ? number_format($tx->zar_value_capital_gain_loss, 2) : 'N/A'
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'headers' => array_keys($csvData->first() ?? []),
                'rows' => $csvData,
                'filename' => "sars-transactions-{$year}" . ($walletId ? "-wallet-{$walletId}" : "") . ".csv"
            ],
            'message' => 'Export data generated successfully'
        ]);
    }
}
