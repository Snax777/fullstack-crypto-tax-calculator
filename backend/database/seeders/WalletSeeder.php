<?php

namespace Database\Seeders;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 3 mock wallets
        $wallets = [
            [
                'name' => 'Bitcoin Wallet',
                'address' => 'bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh',
                'crypto_type' => 'BTC',
                'balance' => 2.5
            ],
            [
                'name' => 'Ethereum Wallet',
                'address' => '0x742d35Cc6634C0532925a3b844Bc9e0BBF0e0C9D',
                'crypto_type' => 'ETH',
                'balance' => 15.75
            ]
        ];

        foreach ($wallets as $walletData) {
            $wallet = Wallet::create($walletData);

            // Add some transactions to each wallet
            $transactions = [
                ['type' => 'buy', 'amount' => 1.5, 'price_at_open' => 25000, 'transaction_date' => Carbon::create(2023, 3, 15)],
                ['type' => 'sell', 'amount' => 0.5, 'price_at_open' => 25000, 'price_at_close' => 55000, 'transaction_date' => Carbon::create(2023, 6, 20)],
                ['type' => 'buy', 'amount' => 10.0, 'price_at_open' => 2000, 'transaction_date' => Carbon::create(2023, 9, 10)],
                ['type' => 'buy', 'amount' => 0.5, 'price_at_open' => 3210, 'transaction_date' => Carbon::create(2024, 1, 5)],
                ['type' => 'sell', 'amount' => 0.5, 'price_at_open' => 2000, 'price_at_close' => 5500, 'transaction_date' => Carbon::create(2024, 2, 15)],
            ];

            foreach ($transactions as $txData) {
                $wallet->transactions()->create($txData);
            }
        }

        $this->command->info('2 wallets with transactions created successfully!');
    }
}
