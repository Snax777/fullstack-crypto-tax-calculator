<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Collection;

class FIFOCalculatorService
{
  public function calculate(string $sessionId): array
  {
    $transactions = Transaction::where('session_id', $sessionId)->orderBy('date')->get();

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
      'total_gain' => $totalGain,
      'coins' => $results,
    ];
  }

  public function calculateCoinFIFO(string $coin, Collection $transactions): array
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
}
