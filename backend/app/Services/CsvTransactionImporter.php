<?php

namespace App\Services;

use League\Csv\Reader;
use App\Models\Transaction;

class CsvTransactionImporter
{
    public function import($filePath)
    {
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);

        $records = $csv->getRecords(); // ← CSV rows

        $saved = [];

        foreach ($records as $record) {  // ← THE LOOP YOU WERE LOOKING FOR

            $transaction = Transaction::create([
                'date' => $record['date'],
                'asset' => $record['asset'],
                'type' => $record['type'],
                'quantity' => $record['quantity'],
                'price' => $record['price'],
                'total' => $record['total'],
            ]);

            $saved[] = $transaction;
        }

        return $saved;
    }
}
