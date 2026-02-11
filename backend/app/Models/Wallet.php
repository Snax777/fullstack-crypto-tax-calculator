<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    // Fields that can be filled from forms
    protected $fillable = [
        'name',
        'address',
        'crypto_type',
        'balance'
    ];

    // Relationship: A wallet has many transactions
    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    // Calculate total profit made in a given year (for tax purposes)
    public function calculateTotalProfitLossYear($year = null) {
        $query = $this->transactions();

        if ($year) {
            $query->whereYear('transaction_date', $year);
        }

        $transactions = $query->get();

        return $transactions->sum(function ($transaction) {
            return $transaction->zar_value_capital_gain_loss ?? 0;
        });
    }

    // Determines if individual is eligible to pay tax
    public function isSarsTaxEligible($year, $threshold = 95_750) {
        $totalProfitLoss = $this->calculateTotalProfitLossYear($year);

        if ($totalProfitLoss) {
            return $totalProfitLoss >= $threshold;
        }

        return false;
    }

    // Determines how much tax an individual should pay
    public function calculateIncomeTax($year) {
        $isTaxEligible = $this->isSarsTaxEligible($year);

        if ($isTaxEligible) {
            $totalProfitLossYear = $this->calculateTotalProfitLossYear($year);

            if (1 <= $totalProfitLossYear && 237_100 >= $totalProfitLossYear) {
                return [
                    '18% of the profit earned must be paid',
                    0.18,
                    $totalProfitLossYear * 0.18
                ];
            } else if (237_101 <= $totalProfitLossYear && 370_500 >= $totalProfitLossYear) {
                return [
                    '26% of the profit earned + ZAR42 678 must be paid',
                    0.26,
                    42_678 + ($totalProfitLossYear * 0.26)
                ];
            } else if (370_501 <= $totalProfitLossYear && 512_800 >= $totalProfitLossYear) {
                return [
                    '31% of the profit earned + ZAR77 362 must be paid',
                    0.31,
                    77_678 + ($totalProfitLossYear * 0.31)
                ];
            } else if (512_801 <= $totalProfitLossYear && 673_000 >= $totalProfitLossYear) {
                return [
                    '36% of the profit earned + ZAR121 475 must be paid',
                    0.36,
                    121_475 + ($totalProfitLossYear * 0.36)
                ];
            } else if (673_001 <= $totalProfitLossYear && 857_900 >= $totalProfitLossYear) {
                return [
                    '39% of the profit earned + ZAR179 147 must be paid',
                    0.39,
                    179_147 + ($totalProfitLossYear * 0.39)
                ];
            } else if (857_901 <= $totalProfitLossYear && 1_817_000 >= $totalProfitLossYear) {
                return [
                    '41% of the profit earned + ZAR251 258 must be paid',
                    0.41,
                    251_258 + ($totalProfitLossYear * 0.41)
                ];
            } else if (1_817_001 <= $totalProfitLossYear) {
                return [
                    '45% of the profit earned + ZAR644 489 must be paid',
                    0.45,
                    644_489 + ($totalProfitLossYear * 0.45)
                ];
            }

            return [
                '0% of the profit earned must be paid',
                0,
                $totalProfitLossYear * 0
            ];
        }
    }
}
