<?php

namespace App\Services;

class TaxCalculatorService
{
  // SARS constants for 2024/2025 tax year
  const ANNUAL_EXCLUSION = 40000; // R40,000
  const INCLUSION_RATE = 0.40;    // 40%

  public function calculateTaxByYear(array $fifoResults): array
  {
    $taxYears = [];
    $totalTaxableAllYears = 0;
    $totalExclusionsApplied = 0;
    $yearsWithTaxableGain = 0;
    $yearsBelowExclusion = 0;

    foreach ($fifoResults['tax_years'] as $yearResult) {
      $totalGain = $yearResult['total_gain'];

      $netGain = max(0, $totalGain - self::ANNUAL_EXCLUSION);

      $taxableGain = $netGain * self::INCLUSION_RATE;

      if ($taxableGain > 0) {
        $yearsWithTaxableGain++;
      }
      if ($totalGain > 0 && $totalGain <= self::ANNUAL_EXCLUSION) {
        $yearsBelowExclusion++;
      }

      $exclusionApplied = min($totalGain, self::ANNUAL_EXCLUSION);
      $totalExclusionsApplied += $exclusionApplied;
      $totalTaxableAllYears += $taxableGain;

      $taxYears[] = array_merge($yearResult, [
        'fifo_calculation' => [
          'total_capital_gain' => round($totalGain, 2),
          'annual_exclusion_limit' => self::ANNUAL_EXCLUSION,
          'annual_exclusion_applied' => round($exclusionApplied, 2),
          'net_capital_gain' => round($netGain, 2),
          'inclusion_rate' => self::INCLUSION_RATE,
          'taxable_capital_gain' => round($taxableGain, 2),
        ],
        'breakdown' => $this->generateBreakdown($totalGain, $netGain, $taxableGain)
      ]);
    }

    $overallSummary = array_merge($fifoResults['overall_summary'], [
      'total_taxable_all_years' => round($totalTaxableAllYears, 2),
      'total_exclusions_applied' => round($totalExclusionsApplied, 2),
      'years_with_taxable_gain' => $yearsWithTaxableGain,
      'years_below_exclusion' => $yearsBelowExclusion
    ]);

    return [
      'session_id' => $fifoResults['session_id'],
      'overall_summary' => $overallSummary,
      'tax_years' => $taxYears
    ];
  }

  public function calculateTax(array $fifoResults): array
  {
    $totalGain = $fifoResults['total_gain'];

    $netGain = max(0, $totalGain - self::ANNUAL_EXCLUSION);

    $taxableGain = $netGain * self::INCLUSION_RATE;

    return [
      'session_id' => $fifoResults['session_id'],
      'summary' => [
        'total_capital_gain' => round($totalGain, 2),
        'annual_exclusion' => self::ANNUAL_EXCLUSION,
        'net_capital_gain' => round($netGain, 2),
        'inclusion_rate' => self::INCLUSION_RATE,
        'taxable_capital_gain' => round($taxableGain, 2),
      ],
      'coins' => $fifoResults['coins'],
      'breakdown' => $this->generateBreakdown($totalGain, $netGain, $taxableGain)
    ];
  }

  private function generateBreakdown(float $totalGain, float $netGain, float $taxableGain): array
  {
    return [
      [
        'step' => 1,
        'description' => 'Total Capital Gain from all transactions',
        'calculation' => 'Sum of all gains and losses',
        'amount' => round($totalGain, 2)
      ],
      [
        'step' => 2,
        'description' => 'Less: Annual Exclusion',
        'calculation' => "R" . number_format($totalGain, 2) . " - R" . number_format(self::ANNUAL_EXCLUSION, 2),
        'amount' => round($netGain, 2)
      ],
      [
        'step' => 3,
        'description' => 'Apply Inclusion Rate (40%)',
        'calculation' => "R" . number_format($netGain, 2) . " × 40%",
        'amount' => round($taxableGain, 2)
      ],
      [
        'step' => 4,
        'description' => 'Taxable Capital Gain',
        'calculation' => 'Report this amount on your SARS tax return',
        'amount' => round($taxableGain, 2),
        'note' => 'Add this to your taxable income. SARS will calculate your final tax based on your total income and tax bracket.'
      ]
    ];
  }
}
