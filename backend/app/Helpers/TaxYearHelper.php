<?php

namespace App\Helpers;

use Carbon\Carbon;

class TaxYearHelper
{
    /**
     * Get the tax year for a given date (SA Tax Year: 1 March to 28/29 February)
     */
    public static function getTaxYear($date): int
    {
        $d = $date instanceof Carbon ? $date : Carbon::parse($date);
        return ($d->month >= 3) ? $d->year : $d->year - 1;
    }

    /**
     * Group transactions by tax year
     */
    public static function groupByTaxYear($transactions): array
    {
        $grouped = [];

        foreach ($transactions as $transaction) {
            $taxYear = self::getTaxYear($transaction->date);
            
            if (!isset($grouped[$taxYear])) {
                $grouped[$taxYear] = [];
            }
            
            $grouped[$taxYear][] = $transaction;
        }

        krsort($grouped);
        return $grouped;
    }

    /**
     * Format grouped transactions for API response
     */
    public static function formatTaxYearResponse(array $grouped): array
    {
        $response = [];

        foreach ($grouped as $taxYear => $transactions) {
            $response[] = [
                'tax_year' => $taxYear,
                'period' => self::getTaxYearPeriod($taxYear),
                'transactions' => $transactions,
                'count' => count($transactions),
            ];
        }

        return $response;
    }

    /**
     * Get the formatted period string for a tax year (handles leap years)
     */
    public static function getTaxYearPeriod(int $taxYear): string
    {
        $startDate = Carbon::create($taxYear, 3, 1)->format('Y-m-d');
        $endDate = Carbon::create($taxYear + 1, 2, 1)->endOfMonth()->format('Y-m-d');
        
        return "{$startDate} to {$endDate}";
    }

    /**
     * Get summary statistics for a tax year
     */
    public static function getTaxYearSummary(array $transactions): array
    {
        $summary = [
            'total_buys' => 0,
            'total_sells' => 0,
            'total_buy_value' => 0,
            'total_sell_value' => 0,
            'total_fees' => 0,
            'coins' => [],
        ];

        foreach ($transactions as $transaction) {
            if ($transaction->type === 'BUY') {
                $summary['total_buys']++;
                $summary['total_buy_value'] += $transaction->price_zar * $transaction->quantity;
            } elseif ($transaction->type === 'SELL') {
                $summary['total_sells']++;
                $summary['total_sell_value'] += $transaction->price_zar * $transaction->quantity;
            }

            $summary['total_fees'] += $transaction->fee ?? 0;

            if (!in_array($transaction->coin, $summary['coins'])) {
                $summary['coins'][] = $transaction->coin;
            }
        }

        return $summary;
    }
}