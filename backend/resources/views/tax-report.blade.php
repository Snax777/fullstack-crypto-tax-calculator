<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Crypto Tax Report</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'DejaVu Sans', sans-serif;
      font-size: 11px;
      line-height: 1.6;
      color: #333;
      padding: 20px;
    }

    .header {
      text-align: center;
      margin-bottom: 30px;
      border-bottom: 3px solid #2563eb;
      padding-bottom: 15px;
    }

    .header h1 {
      font-size: 24px;
      color: #1e40af;
      margin-bottom: 5px;
    }

    .header p {
      font-size: 12px;
      color: #64748b;
    }

    .meta-info {
      background: #f1f5f9;
      padding: 10px;
      border-radius: 5px;
      margin-bottom: 20px;
    }

    .meta-info table {
      width: 100%;
    }

    .meta-info td {
      padding: 3px 5px;
    }

    .section {
      margin-bottom: 25px;
      page-break-inside: avoid;
    }

    .section-title {
      background: #2563eb;
      color: white;
      padding: 8px 12px;
      font-size: 14px;
      font-weight: bold;
      margin-bottom: 10px;
      border-radius: 3px;
    }

    .summary-box {
      background: #fef3c7;
      border: 2px solid #f59e0b;
      padding: 15px;
      margin-bottom: 15px;
      border-radius: 5px;
    }

    .summary-box .big-number {
      font-size: 28px;
      font-weight: bold;
      color: #92400e;
      margin: 10px 0;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin: 10px 0;
    }

    table th {
      background: #e2e8f0;
      padding: 8px;
      text-align: left;
      font-weight: bold;
      border-bottom: 2px solid #cbd5e1;
    }

    table td {
      padding: 6px 8px;
      border-bottom: 1px solid #e2e8f0;
    }

    table tr:last-child td {
      border-bottom: none;
    }

    .text-right {
      text-align: right;
    }

    .text-center {
      text-align: center;
    }

    .breakdown-step {
      margin: 10px 0;
      padding: 10px;
      background: #f8fafc;
      border-left: 4px solid #3b82f6;
    }

    .breakdown-step .step-number {
      display: inline-block;
      width: 25px;
      height: 25px;
      background: #3b82f6;
      color: white;
      border-radius: 50%;
      text-align: center;
      line-height: 25px;
      font-weight: bold;
      margin-right: 10px;
    }

    .instructions {
      background: #dbeafe;
      border: 1px solid #3b82f6;
      padding: 15px;
      margin: 20px 0;
      border-radius: 5px;
    }

    .instructions h3 {
      color: #1e40af;
      margin-bottom: 10px;
    }

    .footer {
      margin-top: 40px;
      padding-top: 15px;
      border-top: 2px solid #e2e8f0;
      font-size: 9px;
      color: #64748b;
      text-align: center;
    }

    .page-break {
      page-break-after: always;
    }

    .coin-badge {
      display: inline-block;
      background: #3b82f6;
      color: white;
      padding: 2px 8px;
      border-radius: 3px;
      font-weight: bold;
      font-size: 10px;
    }

    .status-badge {
      display: inline-block;
      padding: 2px 8px;
      border-radius: 3px;
      font-size: 10px;
      font-weight: bold;
    }

    .status-current {
      background: #dcfce7;
      color: #166534;
    }

    .status-previous {
      background: #e0e7ff;
      color: #3730a3;
    }
  </style>
</head>

<body>
  {{-- Header --}}
  <div class="header">
    <h1>🧮 CRYPTO TAX CALCULATOR</h1>
    <p>Capital Gains Tax Report - South African Revenue Service (SARS)</p>
  </div>

  {{-- Meta Information --}}
  <div class="meta-info">
    <table>
      <tr>
        <td><strong>Report Generated:</strong></td>
        <td>{{ $generated_at }}</td>
        <td><strong>Session ID:</strong></td>
        <td>{{ $session_id }}</td>
      </tr>
      <tr>
        <td><strong>Tax Years Covered:</strong></td>
        <td>{{ $overall_summary['total_years'] }} years</td>
        <td><strong>Total Transactions:</strong></td>
        <td>{{ $overall_summary['total_transactions'] }}</td>
      </tr>
    </table>
  </div>

  {{-- Overall Summary --}}
  <div class="section">
    <div class="section-title">OVERALL SUMMARY</div>

    <div class="summary-box">
      <p style="font-size: 14px; margin-bottom: 5px;"><strong>TOTAL TAXABLE CAPITAL GAIN (All Years)</strong></p>
      <div class="big-number">R {{ number_format($overall_summary['total_taxable_all_years'], 2) }}</div>
      <p style="font-size: 10px; color: #78350f;">
        Add this amount to your taxable income when filing your SARS tax returns
      </p>
    </div>

    <table>
      <tr>
        <th>Metric</th>
        <th class="text-right">Amount (ZAR)</th>
      </tr>
      <tr>
        <td>Total Capital Gain (All Years)</td>
        <td class="text-right">R {{ number_format($overall_summary['total_capital_gain_all_years'], 2) }}</td>
      </tr>
      <tr>
        <td>Total Annual Exclusions Applied</td>
        <td class="text-right">R {{ number_format($overall_summary['total_exclusions_applied'], 2) }}</td>
      </tr>
      <tr>
        <td>Years with Taxable Gains</td>
        <td class="text-right">{{ $overall_summary['years_with_taxable_gain'] }} / {{ $overall_summary['total_years'] }}</td>
      </tr>
      <tr>
        <td>Years Below Exclusion Threshold</td>
        <td class="text-right">{{ $overall_summary['years_below_exclusion'] }} / {{ $overall_summary['total_years'] }}</td>
      </tr>
    </table>
  </div>

  {{-- Tax Years Detail --}}
  @foreach($tax_years as $index => $year)
  @if($index > 0)
  <div class="page-break"></div>
  @endif

  <div class="section">
    <div class="section-title">
      TAX YEAR {{ $year['tax_year'] }}/{{ $year['tax_year'] + 1 }}
      <span class="status-badge status-{{ $year['status'] }}" style="float: right;">{{ strtoupper($year['status']) }}</span>
    </div>

    <p style="margin-bottom: 15px; color: #64748b;">
      <strong>Period:</strong> {{ $year['period'] }}
    </p>

    {{-- Transaction Summary --}}
    <h3 style="font-size: 12px; margin-bottom: 8px;">Transaction Summary</h3>
    <table style="margin-bottom: 20px;">
      <tr>
        <td><strong>Total Transactions:</strong> {{ $year['transaction_summary']['total_transactions'] }}</td>
        <td><strong>Buys:</strong> {{ $year['transaction_summary']['buys'] }}</td>
        <td><strong>Sells:</strong> {{ $year['transaction_summary']['sells'] }}</td>
      </tr>
      <tr>
        <td colspan="3">
          <strong>Coins:</strong>
          @foreach($year['transaction_summary']['coins'] as $coin)
          <span class="coin-badge">{{ $coin }}</span>
          @endforeach
        </td>
      </tr>
    </table>

    {{-- FIFO Calculation --}}
    <h3 style="font-size: 12px; margin-bottom: 8px;">Capital Gains Calculation (FIFO Method)</h3>
    <table>
      <tr>
        <th>Description</th>
        <th class="text-right">Amount (ZAR)</th>
      </tr>
      <tr>
        <td>Total Capital Gain</td>
        <td class="text-right">R {{ number_format($year['fifo_calculation']['total_capital_gain'], 2) }}</td>
      </tr>
      <tr>
        <td>Less: Annual Exclusion</td>
        <td class="text-right">- R {{ number_format($year['fifo_calculation']['annual_exclusion_applied'], 2) }}</td>
      </tr>
      <tr style="background: #f1f5f9;">
        <td><strong>Net Capital Gain</strong></td>
        <td class="text-right"><strong>R {{ number_format($year['fifo_calculation']['net_capital_gain'], 2) }}</strong></td>
      </tr>
      <tr>
        <td>Inclusion Rate (40%)</td>
        <td class="text-right">× 0.40</td>
      </tr>
      <tr style="background: #fef3c7; font-weight: bold;">
        <td><strong>Taxable Capital Gain</strong></td>
        <td class="text-right"><strong>R {{ number_format($year['fifo_calculation']['taxable_capital_gain'], 2) }}</strong></td>
      </tr>
    </table>

    {{-- Step-by-Step Breakdown --}}
    <h3 style="font-size: 12px; margin: 20px 0 10px 0;">Calculation Breakdown</h3>
    @foreach($year['breakdown'] as $step)
    <div class="breakdown-step">
      <span class="step-number">{{ $step['step'] }}</span>
      <strong>{{ $step['description'] }}</strong><br>
      <span style="color: #64748b; font-size: 10px;">{{ $step['calculation'] }}</span><br>
      <strong style="color: #1e40af;">R {{ number_format($step['amount'], 2) }}</strong>
      @if(isset($step['note']))
      <br><em style="font-size: 10px; color: #64748b;">{{ $step['note'] }}</em>
      @endif
    </div>
    @endforeach

    {{-- Coin Details --}}
    @if(count($year['coins']) > 0)
    <h3 style="font-size: 12px; margin: 20px 0 10px 0;">Coin Breakdown</h3>
    @foreach($year['coins'] as $coin)
    <table style="margin-bottom: 15px;">
      <tr style="background: #f8fafc;">
        <th colspan="2">
          <span class="coin-badge">{{ $coin['coin'] }}</span>
          Total Gain: R {{ number_format($coin['total_gain'], 2) }}
        </th>
      </tr>
      <tr>
        <td>Carried Forward from Previous Year</td>
        <td class="text-right">{{ $coin['carried_forward_quantity'] }} {{ $coin['coin'] }}</td>
      </tr>
      <tr>
        <td>Purchased This Year</td>
        <td class="text-right">{{ $coin['purchased_this_year'] }} {{ $coin['coin'] }}</td>
      </tr>
      <tr>
        <td>Sold This Year</td>
        <td class="text-right">{{ $coin['sold_this_year'] }} {{ $coin['coin'] }}</td>
      </tr>
      <tr style="background: #f1f5f9;">
        <td><strong>Remaining for Next Year</strong></td>
        <td class="text-right"><strong>{{ $coin['remaining_quantity'] }} {{ $coin['coin'] }}</strong></td>
      </tr>
    </table>
    @endforeach
    @endif
  </div>
  @endforeach

  {{-- Instructions --}}
  <div class="section">
    <div class="section-title">INSTRUCTIONS FOR SARS TAX RETURN</div>

    <div class="instructions">
      <h3>How to Report Your Crypto Gains:</h3>
      <ol style="margin-left: 20px; margin-top: 10px;">
        <li style="margin-bottom: 8px;">
          <strong>For each tax year</strong>, add the "Taxable Capital Gain" to your taxable income on your SARS return
        </li>
        <li style="margin-bottom: 8px;">
          SARS will calculate your final tax based on your <strong>total taxable income</strong> and applicable tax bracket
        </li>
        <li style="margin-bottom: 8px;">
          Keep this report and all supporting transaction records for <strong>5 years</strong> as required by SARS
        </li>
        <li style="margin-bottom: 8px;">
          This calculator uses the <strong>FIFO (First-In-First-Out)</strong> method approved by SARS
        </li>
        <li style="margin-bottom: 8px;">
          The <strong>R40,000 annual exclusion</strong> and <strong>40% inclusion rate</strong> are automatically applied
        </li>
      </ol>
    </div>

    <p style="margin-top: 15px; padding: 10px; background: #fef2f2; border-left: 4px solid #dc2626;">
      <strong>⚠️ Important:</strong> This report is for informational purposes only and does not constitute
      professional tax advice. Consult a registered tax practitioner for complex situations or if you're
      unsure about any aspect of your tax obligations.
    </p>
  </div>

  {{-- Footer --}}
  <div class="footer">
    <p><strong>Crypto Tax Calculator</strong> | Generated on {{ $generated_at }}</p>
    <p>Calculation Method: FIFO (First-In-First-Out) | SARS-Compliant</p>
    <p>Session ID: {{ $session_id }}</p>
  </div>
</body>

</html>