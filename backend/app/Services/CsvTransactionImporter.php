<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;

class CsvTransactionImporter
{
  public function importFromCSV(string $filePath): array
    {
    $csv = Reader::from($filePath, 'r');
        $csv->setHeaderOffset(0);

    $records = $csv->getRecords();
    $sessionId = 'calc-' . Str::uuid();

    $transactions = [];
    $errors = [];
    $rowNumber = 1;

    foreach ($records as $record) {
      $rowNumber++;

      $record = $this->normalizeColumns($record);

      $validator = Validator::make($record, Transaction::rules());

      if ($validator->fails()) {
        $errors[] = [
          'row' => $rowNumber,
          'errors' => $validator->errors()->all()
        ];
        continue;
      }

      try {
        $transaction = Transaction::create([
          'date' => $record['date'],
          'type' => strtoupper($record['type']),
          'coin' => strtoupper($record['coin']),
          'quantity' => $record['quantity'],
          'price_zar' => $record['price_zar'],
          'fee' => $record['fee'] ?? null,
          'session_id' => $sessionId
        ]);

        $transactions[] = $transaction;
      } catch (\Exception $e) {
        $errors[] = [
          'row' => $rowNumber,
          'errors' => ['Failed to create transaction: ' . $e->getMessage()]
        ];
      }
    }

    return [
      'session_id' => $sessionId,
      'count' => count($transactions),
      'errors' => $errors,
      'transactions' => $transactions
    ];
  }

  private function normalizeColumns(array $record): array
  {
    $normalized = [];

    $columnMap = [
      'date' => ['date', 'transaction_date', 'datetime', 'Date', 'DATE'],
      'type' => ['type', 'transaction_type', 'Type', 'TYPE'],
      'coin' => ['coin', 'cryptocurrency', 'crypto', 'symbol', 'Coin', 'COIN', 'Currency'],
      'quantity' => ['quantity', 'amount', 'qty', 'Quantity', 'QUANTITY', 'Amount'],
      'price_zar' => ['price_zar', 'price', 'price_in_zar', 'zar_price', 'Price (ZAR)', 'Price', 'PRICE'],
      'fee' => ['fee', 'transaction_fee', 'Fee', 'FEE', 'fees']
    ];

    foreach ($columnMap as $standardName => $variations) {
      foreach ($variations as $variation) {
        if (isset($record[$variation])) {
          $normalized[$standardName] = $record[$variation];
          break;
        }
      }
    }

    return $normalized;
  }

  // public function importFromExcel(string $filePath): array
  // {
  //   throw new \Exception('Excel import not yet implemented. Please convert to CSV first.');
  // }
}
