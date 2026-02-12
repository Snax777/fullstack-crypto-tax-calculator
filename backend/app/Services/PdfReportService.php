<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PdfReportService
{
  /**
   * Generate PDF report from tax calculation results
   */
  public function generateTaxReport(array $taxResults): \Barryvdh\DomPDF\PDF
  {
    $data = [
      'session_id' => $taxResults['session_id'],
      'generated_at' => Carbon::now()->format('Y-m-d H:i:s'),
      'overall_summary' => $taxResults['overall_summary'],
      'tax_years' => $taxResults['tax_years']
    ];

    $pdf = Pdf::loadView('tax-report', $data);

    $pdf->setPaper('a4', 'portrait');

    return $pdf;
  }

  /**
   * Generate PDF for a specific tax year
   */
  public function generateYearReport(array $yearData, string $sessionId): \Barryvdh\DomPDF\PDF
  {
    $data = [
      'session_id' => $sessionId,
      'generated_at' => Carbon::now()->format('Y-m-d H:i:s'),
      'year_data' => $yearData
    ];

    $pdf = Pdf::loadView('pdf.year-report', $data);
    $pdf->setPaper('a4', 'portrait');

    return $pdf;
  }
}
