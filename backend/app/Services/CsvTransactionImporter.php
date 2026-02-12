<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;
use Maatwebsite\Excel\Facades\Excel;

class CsvTransactionImporter
{
    public function importFromFile(string $filePath): array
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (in_array($extension, ['xlsx', 'xls'])) {
            return $this->importFromExcel($filePath);
        }

        return $this->importFromCSV($filePath);
    }

    public function importFromCSV(string $filePath): array
    {
        $csv = Reader::createFromPath($filePath, 'r');
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

    public function importFromExcel(string $filePath): array
    {
        // Load spreadsheet
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        
        // Get highest row
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        
        // Read headers from first row
        $headers = [];
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $headers[] = trim($worksheet->getCellByColumnAndRow($col, 1)->getValue() ?? '');
        }
        
        $sessionId = 'calc-' . Str::uuid();
        $transactions = [];
        $errors = [];

        // Read data rows (starting from row 2)
        for ($row = 2; $row <= $highestRow; $row++) {
            $record = [];
            
            // Read each cell
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $cell = $worksheet->getCellByColumnAndRow($col, $row);
                $value = $cell->getValue();
                
                // Handle dates
                if ($value instanceof \DateTime) {
                    $value = $value->format('Y-m-d');
                } elseif (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
                    $value = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
                }
                
                $record[$headers[$col - 1]] = $value;
            }

            // Skip empty rows
            if (empty(array_filter($record))) {
                continue;
            }

            // Normalize column names
            $record = $this->normalizeColumns($record);
            
            // DEBUG: Log what we got
            if ($rowNumber == 2) {
                $errors[] = [
                    'row' => 'DEBUG',
                    'raw_record_keys' => array_keys($record),
                    'raw_record' => $record,
                    'normalized_record' => $record
                ];
            }

            $validator = Validator::make($record, Transaction::rules());

            if ($validator->fails()) {
                $errors[] = [
                    'row' => $row,
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
                    'row' => $row,
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
                    $value = $record[$variation];
                    
                    // Handle dates
                    if ($standardName === 'date') {
                        // Excel DateTime object
                        if ($value instanceof \DateTimeInterface) {
                            $value = $value->format('Y-m-d');
                        }
                        // Excel serial number (numeric date)
                        elseif (is_numeric($value) && $value > 40000) {
                            $value = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
                        }
                        // Already a string date - keep it
                    }
                    
                    // Convert numeric strings to proper numbers
                    if (in_array($standardName, ['quantity', 'price_zar', 'fee']) && is_string($value)) {
                        $value = is_numeric($value) ? (float)$value : $value;
                    }
                    
                    $normalized[$standardName] = $value;
                    break;
                }
            }
        }

        return $normalized;
    }
}