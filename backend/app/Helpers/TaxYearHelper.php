<?php

namespace App\Helpers;

use Carbon\Carbon;

class TaxYearHelper
{
  public static function getTaxYear($date): int
  {
    try {
      $d = $date instanceof Carbon ? $date : Carbon::parse($date);

      // If month >= 3 (March or later), tax year is current year
      // If month < 3 (Jan or Feb), tax year is previous year
      return ($d->month >= 3) ? $d->year : $d->year - 1;
    } catch (\Exception $e) {
      throw new \InvalidArgumentException("Invalid date format: {$date}");
    }
  }

  public static function getCurrentTaxYear(): int
  {
    return self::getTaxYear(Carbon::now());
  }

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

  public static function getTaxYearPeriod(int $taxYear): string
  {
    $startDate = Carbon::create($taxYear, 3, 1)->format('Y-m-d');

    $endDate = Carbon::create($taxYear + 1, 2, 1)->endOfMonth()->format('Y-m-d');

    return "{$startDate} to {$endDate}";
  }

  public static function getTaxYearStart(int $taxYear): Carbon
  {
    return Carbon::create($taxYear, 3, 1, 0, 0, 0);
  }

  public static function getTaxYearEnd(int $taxYear): Carbon
  {
    return Carbon::create($taxYear + 1, 3, 1, 0, 0, 0)->subDay()->endOfDay();
  }

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

  public static function isInTaxYear($date, int $taxYear): bool
  {
    return self::getTaxYear($date) === $taxYear;
  }

  public static function getTaxYearStatus(int $taxYear): string
  {
    $currentTaxYear = self::getCurrentTaxYear();

    if ($taxYear === $currentTaxYear) {
      return 'current';
    } elseif ($taxYear < $currentTaxYear) {
      return 'previous';
    } else {
      return 'future';
    }
  }
}
