<?php

namespace App\Services;

use App\Models\Transaction;
use App\Helpers\TaxYearHelper;
use Illuminate\Support\Collection;

class FIFOCalculatorService
{
  /**
   * Calculate FIFO gains for all transactions (combined, no year grouping)
   */
  public function calculate(string $sessionId): array
  {
    $transactions = Transaction::where('session_id', $sessionId)
      ->orderBy('date')
      ->get();

    if ($transactions->isEmpty()) {
      throw new \Exception('No transactions found for session: ' . $sessionId);
    }

    $coinGroups = $transactions->groupBy('coin');

    $results = [];
    $totalGain = 0;

    foreach ($coinGroups as $coin => $coinTransactions) {
      $coinResult = $this->calculateCoinFIFO($coin, $coinTransactions);
      $results[] = $coinResult;
      $totalGain += $coinResult['total_gain'];
    }

    return [
      'session_id' => $sessionId,
      'total_gain' => round($totalGain, 2),
      'coins' => $results
    ];
  }

  /**
   * Calculate FIFO gains grouped by tax year
   */
  public function calculateByTaxYear(string $sessionId): array
  {
    $transactions = Transaction::where('session_id', $sessionId)
      ->orderBy('date')
      ->get();

    if ($transactions->isEmpty()) {
      throw new \Exception('No transactions found for session: ' . $sessionId);
    }

    $yearGroups = TaxYearHelper::groupByTaxYear($transactions);

    $carryForwardQueues = [];

    $yearResults = [];

    $allCoins = $transactions->pluck('coin')->unique();
    foreach ($allCoins as $coin) {
      $carryForwardQueues[$coin] = [];
    }

    ksort($yearGroups);

    foreach ($yearGroups as $taxYear => $yearTransactions) {

      $yearResult = $this->calculateYearFIFO(
        $taxYear,
        collect($yearTransactions),
        $carryForwardQueues
      );

      $yearResults[] = $yearResult;
    }

    $overallSummary = $this->calculateOverallSummary($yearResults);

    return [
      'session_id' => $sessionId,
      'overall_summary' => $overallSummary,
      'tax_years' => $yearResults
    ];
  }

  private function calculateYearFIFO(
    int $taxYear,
    Collection $transactions,
    array &$carryForwardQueues
  ): array {
    $coinGroups = $transactions->groupBy('coin');

    $coinResults = [];
    $totalGain = 0;

    foreach ($coinGroups as $coin => $coinTransactions) {
      if (!isset($carryForwardQueues[$coin])) {
        $carryForwardQueues[$coin] = [];
      }

      $coinResult = $this->calculateCoinFIFOWithCarryForward(
        $coin,
        $coinTransactions,
        $carryForwardQueues[$coin]
      );

      $coinResults[] = $coinResult;
      $totalGain += $coinResult['total_gain'];
    }

    $transactionSummary = $this->getTransactionSummary($transactions);

    $status = TaxYearHelper::getTaxYearStatus($taxYear);

    $result = [
      'tax_year' => $taxYear,
      'period' => TaxYearHelper::getTaxYearPeriod($taxYear),
      'status' => $status,
      'transaction_summary' => $transactionSummary,
      'total_gain' => round($totalGain, 2),
      'coins' => $coinResults
    ];

    return $result;
  }

  private function getTransactionSummary(Collection $transactions): array
  {
    $buys = $transactions->where('type', 'BUY');
    $sells = $transactions->where('type', 'SELL');
    $coins = $transactions->pluck('coin')->unique()->values()->toArray();

    return [
      'total_transactions' => $transactions->count(),
      'buys' => $buys->count(),
      'sells' => $sells->count(),
      'coins' => $coins,
      'total_buy_value' => round($buys->sum(function ($tx) {
        return $tx->price_zar * $tx->quantity;
      }), 2),
      'total_sell_value' => round($sells->sum(function ($tx) {
        return $tx->price_zar * $tx->quantity;
      }), 2),
      'total_fees' => round($transactions->sum('fee'), 2)
    ];
  }

  private function calculateCoinFIFOWithCarryForward(
    string $coin,
    Collection $transactions,
    array &$purchaseQueue
  ): array {
    $buys = $transactions->where('type', 'BUY')->values();
    $sales = $transactions->where('type', 'SELL')->values();

    $carriedForwardQuantity = array_sum(array_column($purchaseQueue, 'remaining'));

    foreach ($buys as $buy) {
      $purchaseQueue[] = [
        'date' => $buy->date,
        'quantity' => (float) $buy->quantity,
        'price_zar' => (float) $buy->price_zar,
        'fee' => (float) ($buy->fee ?? 0),
        'remaining' => (float) $buy->quantity,
      ];
    }

    $saleDetails = [];
    $totalGain = 0;

    foreach ($sales as $sale) {
      $saleQuantity = (float) $sale->quantity;
      $salePrice = (float) $sale->price_zar;
      $saleFee = (float) ($sale->fee ?? 0);
      $saleDate = $sale->date;

      $saleGain = 0;
      $matches = [];

      while ($saleQuantity > 0 && count($purchaseQueue) > 0) {
        $buy = &$purchaseQueue[0];

        $matchQuantity = min($saleQuantity, $buy['remaining']);

        $matchCost = ($buy['price_zar'] * $matchQuantity) +
          ($buy['fee'] * ($matchQuantity / $buy['quantity']));

        $matchRevenue = ($salePrice * $matchQuantity) -
          ($saleFee * ($matchQuantity / $sale->quantity));

        $matchGain = $matchRevenue - $matchCost;

        $matches[] = [
          'buy_date' => $buy['date'],
          'quantity' => $matchQuantity,
          'buy_price' => $buy['price_zar'],
          'sell_price' => $salePrice,
          'cost' => round($matchCost, 2),
          'revenue' => round($matchRevenue, 2),
          'gain' => round($matchGain, 2)
        ];

        $saleGain += $matchGain;
        $saleQuantity -= $matchQuantity;
        $buy['remaining'] -= $matchQuantity;

        if ($buy['remaining'] <= 0.00000001) {
          array_shift($purchaseQueue);
        }
      }

      if ($saleQuantity > 0.00000001) {
        throw new \Exception(
          "Insufficient balance for {$coin}. " .
            "Trying to sell {$sale->quantity} but only have " .
            ($sale->quantity - $saleQuantity) . " available."
        );
      }

      $saleDetails[] = [
        'date' => $saleDate,
        'quantity' => (float) $sale->quantity,
        'price' => $salePrice,
        'fee' => $saleFee,
        'gain' => round($saleGain, 2),
        'matches' => $matches
      ];

      $totalGain += $saleGain;
    }

    $remainingQuantity = array_sum(array_column($purchaseQueue, 'remaining'));

    return [
      'coin' => $coin,
      'carried_forward_quantity' => round($carriedForwardQuantity, 8),
      'purchased_this_year' => round($buys->sum('quantity'), 8),
      'sold_this_year' => round($sales->sum('quantity'), 8),
      'remaining_quantity' => round($remainingQuantity, 8),
      'total_buys' => $buys->count(),
      'total_sales' => $sales->count(),
      'total_gain' => round($totalGain, 2),
      'sales' => $saleDetails
    ];
  }

  private function calculateCoinFIFO(string $coin, Collection $transactions): array
  {
    $buys = $transactions->where('type', 'BUY')->values();
    $sales = $transactions->where('type', 'SELL')->values();

    $buyQueue = [];
    foreach ($buys as $buy) {
      $buyQueue[] = [
        'date' => $buy->date,
        'quantity' => (float) $buy->quantity,
        'price_zar' => (float) $buy->price_zar,
        'fee' => (float) ($buy->fee ?? 0),
        'remaining' => (float) $buy->quantity,
      ];
    }

    $saleDetails = [];
    $totalGain = 0;

    foreach ($sales as $sale) {
      $saleQuantity = (float) $sale->quantity;
      $salePrice = (float) $sale->price_zar;
      $saleFee = (float) ($sale->fee ?? 0);
      $saleDate = $sale->date;

      $saleGain = 0;
      $matches = [];

      while ($saleQuantity > 0 && count($buyQueue) > 0) {
        $buy = &$buyQueue[0];
        $matchQuantity = min($saleQuantity, $buy['remaining']);

        $matchCost = ($buy['price_zar'] * $matchQuantity) +
          ($buy['fee'] * ($matchQuantity / $buy['quantity']));

        $matchRevenue = ($salePrice * $matchQuantity) -
          ($saleFee * ($matchQuantity / $sale->quantity));

        $matchGain = $matchRevenue - $matchCost;

        $matches[] = [
          'buy_date' => $buy['date'],
          'quantity' => $matchQuantity,
          'buy_price' => $buy['price_zar'],
          'sell_price' => $salePrice,
          'cost' => round($matchCost, 2),
          'revenue' => round($matchRevenue, 2),
          'gain' => round($matchGain, 2)
        ];

        $saleGain += $matchGain;
        $saleQuantity -= $matchQuantity;
        $buy['remaining'] -= $matchQuantity;

        if ($buy['remaining'] <= 0.00000001) {
          array_shift($buyQueue);
        }
      }

      if ($saleQuantity > 0.00000001) {
        throw new \Exception(
          "Insufficient balance for {$coin}. " .
            "Trying to sell {$sale->quantity} but only have " .
            ($sale->quantity - $saleQuantity) . " available."
        );
      }

      $saleDetails[] = [
        'date' => $saleDate,
        'quantity' => (float) $sale->quantity,
        'price' => $salePrice,
        'fee' => $saleFee,
        'gain' => round($saleGain, 2),
        'matches' => $matches
      ];

      $totalGain += $saleGain;
    }

    return [
      'coin' => $coin,
      'total_buys' => $buys->count(),
      'total_sales' => $sales->count(),
      'total_gain' => round($totalGain, 2),
      'sales' => $saleDetails,
      'remaining_balance' => array_sum(array_column($buyQueue, 'remaining'))
    ];
  }

  private function calculateOverallSummary(array $yearResults): array
  {
    $totalYears = count($yearResults);
    $totalTransactions = 0;
    $totalGainAllYears = 0;
    $yearsWithData = [];

    foreach ($yearResults as $year) {
      $totalTransactions += $year['transaction_summary']['total_transactions'];
      $totalGainAllYears += $year['total_gain'];
      $yearsWithData[] = $year['tax_year'];
    }

    return [
      'total_years' => $totalYears,
      'earliest_tax_year' => min($yearsWithData),
      'latest_tax_year' => max($yearsWithData),
      'total_transactions' => $totalTransactions,
      'total_capital_gain_all_years' => round($totalGainAllYears, 2)
    ];
  }
}
