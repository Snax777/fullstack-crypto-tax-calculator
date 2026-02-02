<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Services\CsvTransactionImporter;

class TransactionUploadController extends Controller
{
    public function upload(Request $request, CsvTransactionImporter $importer)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('file');

        $transactions = $importer->import($file->getPathname());

        return response()->json([
            'message' => 'CSV imported successfully',
            'data' => $transactions
        ]);
    }
}
