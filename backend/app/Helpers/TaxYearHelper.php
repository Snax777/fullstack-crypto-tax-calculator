<?php

namespace App\Helpers;

use Carbon\Carbon;

class TaxYearHelper
{
    /**
     * Get the tax year for a given date
     * SA Tax Year: 1 March to 28/29 February (next year)
     * 
     * @param string|Carbon $date
     * @return int The tax year (e.g., 2023 for period 2023-03-01 to 2024-02-29)
     */
    public static function getTaxYear($date): int
    {
        $d = $date instanceof Carbon ? $date : Carbon::parse($date);
        
        // If month >= 3 (March or later), tax year is current year
        // If month < 3 (Jan or Feb), tax year is previous year
        return ($d->month >= 3) ? $d->year : $d->year - 1;
    }

    /**
     * Group transactions by tax year
     * 
     * @param \Illuminate\Support\Collection $transactions
     * @return array Grouped transactions with tax year as key
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

        // Sort by tax year descending (most recent first)
        krsort($grouped);

        return $grouped;
    }

    /**
     * Format grouped transactions for API response
     * 
     * @param array $grouped Grouped transactions by tax year
     * @return array Formatted response
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
     * Get the formatted period string for a tax year
     * 
     * @param int $taxYear
     * @return string e.g., "2023-03-01 to 2024-02-29"
     */
    public static function getTaxYearPeriod(int $taxYear): string
    {
        $startDate = Carbon::create($taxYear, 3, 1)->format('Y-m-d');
        $endDate = Carbon::create($taxYear + 1, 2, 29)->format('Y-m-d');
        
        return "{$startDate} to {$endDate}";
    }

    /**
     * Get summary statistics for a tax year
     * 
     * @param array $transactions
     * @return array
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

            // Track unique coins
            if (!in_array($transaction->coin, $summary['coins'])) {
                $summary['coins'][] = $transaction->coin;
            }
        }

        return $summary;
    }
}