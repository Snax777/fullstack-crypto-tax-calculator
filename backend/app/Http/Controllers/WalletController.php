<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index()
    {
        $wallets = Wallet::withCount('transactions')->get();
        return response()->json($wallets);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'address' => 'required|unique:wallets|max:255',
            'crypto_type' => 'required|in:BTC,ETH,DOGE,LTC,XRP,ADA',
            'balance' => 'nullable|numeric|min:0'
        ]);

        $wallet = Wallet::create($validated);
        return response()->json($wallet, 201);
    }

    public function show(Wallet $wallet)
    {
        $wallet->load('transactions');
        return response()->json($wallet);
    }

    public function update(Request $request, Wallet $wallet)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'crypto_type' => 'required|in:BTC,ETH,DOGE,LTC,XRP,ADA',
            'balance' => 'nullable|numeric|min:0'
        ]);

        $wallet->update($validated);
        return response()->json($wallet);
    }

    public function destroy(Wallet $wallet)
    {
        $wallet->delete();
        return response()->json(null, 204);
    }
}
