<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Combined Daily Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            font-size: 14px;
        }
        .container {
            width: 100%;
            max-width: 1000px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #004d00;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
            margin-bottom: 15px;
        }
        .header h2, .header h4 {
            margin: 0;
        }
        .header h2 { font-size: 22px; }
        .header h4 { font-size: 16px; }
        .date-info { font-size: 13px; color: #ccc; }

        .table-container { margin-top: 20px; overflow-x: auto; }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            color: #333;
        }
        .report-table thead th, .report-table tfoot td {
            background-color: #003300;
            color: white;
            padding: 8px;
            border: 1px solid #006600;
            text-align: center;
        }
        .report-table tbody td {
            padding: 6px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .report-table tbody tr:nth-of-type(odd) { background-color: #f9f9f9; }
        .report-table tbody tr:nth-of-type(even) { background-color: #ffffff; }
        .item-summary-row td { font-weight: bold; background-color: #e0e0e0; }
        .total-row td { font-weight: bold; background-color: #008000; color: white; }

        /* Extra styles from your second snippet */
        .custom-card { background-color: #006400 !important; color: white; padding: 20px; border-radius: 8px; }
        .custom-card table thead, .custom-card table tfoot { background-color: #004d00 !important; color: white; }
        .custom-card table tbody tr:nth-child(odd) { background-color: #00800033; }
        .report-title-bar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap; }
        .company-name { font-weight: 700; font-size: 1.5rem; color: white; margin: 0; }
        .report-title-bar h4 { margin: 0; color: white; font-weight: 700; }
        .right-info { color: white; font-weight: 600; }
        .print-btn { background-color: #004d00; color: white; border: none; padding: 0.4rem 1rem; border-radius: 5px; cursor: pointer; font-weight: 600; }
        .print-btn:hover { background-color: #003300; }

        .compact-table th, .compact-table td { font-size: 13px; padding: 4px 8px; }

        @media print {
            body { background-color: #fff !important; color: #000; }
            .custom-card { background-color: #fff !important; color: #000 !important; box-shadow: none !important; border: none !important; }
            .custom-card table { border: 1px solid #ccc; }
            .custom-card table th, .custom-card table td { border: 1px solid #ccc; color: #000; }
            .custom-card table thead, .custom-card table tfoot { background-color: #eee !important; color: #000 !important; }
            .custom-card table tbody tr:nth-child(odd) { background-color: #f9f9f9 !important; }
            .report-title-bar h2, .report-title-bar h4, .right-info { color: #000 !important; }
            .print-btn { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="container">

        {{-- Section 1 - Combined Daily Report --}}
        <div class="header">
            <h2 class="company-name">TGK ට්‍රේඩර්ස්</h2>
            <h4>📦 විකුණුම්/බර මත්තෙහි ඉතිරි වාර්තාව</h4>
            <span class="date-info">{{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}</span>
        </div>
          <table class="report-table">
                <thead>
                    <tr>
                        <th rowspan="2">වර්ගය</th>
                        <th colspan="2">මිලදී ගැනීම</th>
                        <th colspan="2">විකුණුම්</th>
                        <th rowspan="2">එකතුව</th>
                        <th colspan="2">ඉතිරි</th>
                    </tr>
                    <tr>
                        <th>මලු</th>
                        <th>බර</th>
                        <th>මලු</th>
                        <th>බර</th>
                        <th>මලු</th>
                        <th>බර</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $grandTotalOriginalPacks = 0;
                        $grandTotalOriginalWeight = 0;
                        $grandTotalSoldPacks = 0;
                        $grandTotalSoldWeight = 0;
                        $grandTotalSalesValue = 0;
                        $grandTotalRemainingPacks = 0;
                        $grandTotalRemainingWeight = 0;
                    @endphp

                    @forelse($dayStartReportData as $item)
                        @php
                            $grandTotalOriginalPacks += $item['original_packs'];
                            $grandTotalOriginalWeight += $item['original_weight'];
                            $grandTotalSoldPacks += $item['sold_packs'];
                            $grandTotalSoldWeight += $item['sold_weight'];
                            $grandTotalSalesValue += $item['total_sales_value'];
                            $grandTotalRemainingPacks += $item['remaining_packs'];
                            $grandTotalRemainingWeight += $item['remaining_weight'];
                        @endphp
                        <tr class="item-summary-row">
                            <td>{{ $item['item_name'] }}</td>
                            <td>{{ number_format($item['original_packs']) }}</td>
                            <td>{{ number_format($item['original_weight'], 2) }}</td>
                            <td>{{ number_format($item['sold_packs']) }}</td>
                            <td>{{ number_format($item['sold_weight'], 2) }}</td>
                            <td>Rs. {{ number_format($item['total_sales_value'], 2) }}</td>
                            <td>{{ number_format($item['remaining_packs']) }}</td>
                            <td>{{ number_format($item['remaining_weight'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">දත්ත නොමැත.</td>
                        </tr>
                    @endforelse

                    <tr class="total-row">
                        <td colspan="1">සමස්ත එකතුව:</td>
                        <td>{{ number_format($grandTotalOriginalPacks) }}</td>
                        <td>{{ number_format($grandTotalOriginalWeight, 2) }}</td>
                        <td>{{ number_format($grandTotalSoldPacks) }}</td>
                        <td>{{ number_format($grandTotalSoldWeight, 2) }}</td>
                        <td>Rs. {{ number_format($grandTotalSalesValue, 2) }}</td>
                        <td>{{ number_format($grandTotalRemainingPacks) }}</td>
                        <td>{{ number_format($grandTotalRemainingWeight, 2) }}</td>
                    </tr>
                </tbody>
            </table>

        {{-- Section 2 - GRN Report --}}
        <div class="header">
            <h4>📦 විකුණුම්/බර මත්තෙහි ඉතිරි වාර්තාව (GRN)</h4>
            <span class="date-info">{{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}</span>
        </div>
         <table class="report-table">
                <thead>
                    <tr>
                        <th rowspan="2">දින</th>
                        <th rowspan="2">GRN කේතය</th>
                        <th rowspan="2">වර්ගය</th>
                        <th colspan="2">මිලදී ගැනීම</th>
                        <th colspan="2">විකුණුම්</th>
                        <th rowspan="2">එකතුව</th>
                        <th colspan="2">ඉතිරි</th>
                    </tr>
                    <tr>
                        <th>මලු</th>
                        <th>බර</th>
                        <th>මලු</th>
                        <th>බර</th>
                        <th>මලු</th>
                        <th>බර</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $grnGrandTotalOriginalPacks = 0;
                        $grnGrandTotalOriginalWeight = 0;
                        $grnGrandTotalSoldPacks = 0;
                        $grnGrandTotalSoldWeight = 0;
                        $grnGrandTotalSalesValue = 0;
                        $grnGrandTotalRemainingPacks = 0;
                        $grnGrandTotalRemainingWeight = 0;
                    @endphp
                    @forelse($grnReportData as $item)
                        @php
                            $grnGrandTotalOriginalPacks += $item['original_packs'];
                            $grnGrandTotalOriginalWeight += $item['original_weight'];
                            $grnGrandTotalSoldPacks += $item['sold_packs'];
                            $grnGrandTotalSoldWeight += $item['sold_weight'];
                            $grnGrandTotalSalesValue += $item['total_sales_value'];
                            $grnGrandTotalRemainingPacks += $item['remaining_packs'];
                            $grnGrandTotalRemainingWeight += $item['remaining_weight'];
                        @endphp
                        <tr>
                            <td>{{ $item['date'] }}</td>
                            <td>{{ $item['grn_code'] }}</td>
                            <td>{{ $item['item_name'] }}</td>
                            <td>{{ number_format($item['original_packs']) }}</td>
                            <td>{{ number_format($item['original_weight'], 2) }}</td>
                            <td>{{ number_format($item['sold_packs']) }}</td>
                            <td>{{ number_format($item['sold_weight'], 2) }}</td>
                            <td>Rs. {{ number_format($item['total_sales_value'], 2) }}</td>
                            <td>{{ number_format($item['remaining_packs']) }}</td>
                            <td>{{ number_format($item['remaining_weight'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">GRN දත්ත නොමැත.</td>
                        </tr>
                    @endforelse
                    <tr class="total-row">
                        <td colspan="3">සමස්ත එකතුව:</td>
                        <td>{{ number_format($grnGrandTotalOriginalPacks) }}</td>
                        <td>{{ number_format($grnGrandTotalOriginalWeight, 2) }}</td>
                        <td>{{ number_format($grnGrandTotalSoldPacks) }}</td>
                        <td>{{ number_format($grnGrandTotalSoldWeight, 2) }}</td>
                        <td>Rs. {{ number_format($grnGrandTotalSalesValue, 2) }}</td>
                        <td>{{ number_format($grnGrandTotalRemainingPacks) }}</td>
                        <td>{{ number_format($grnGrandTotalRemainingWeight, 2) }}</td>
                    </tr>
                </tbody>
            </table>

        {{-- Section 3 - Sales History --}}
        <div class="header">
            <h4>විකුණුම් ඉතිහාසය</h4>
            <span class="date-info">{{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}</span>
        </div>
        {{-- (keep your sales history table here as before) --}}

        {{-- Section 4 - New Compact Sales by GRN/Filters --}}
        <div class="custom-card mt-4">
            <div class="report-title-bar">
                <h2 class="company-name">TGK ට්‍රේඩර්ස්</h2>
                <h4>මුළු අයිතම විකිණුම් – ප්‍රමාණ අනුව</h4>
                <span class="right-info">{{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}</span>
              
            </div>


            <table class="table table-sm table-bordered table-striped compact-table">
                <thead>
                    <tr>
                        <th>අයිතම කේතය</th>
                        <th>වර්ගය</th>
                        <th>මලු</th>
                        <th>බර</th>
                        <th>එකතුව</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $total_packs = 0;
                        $total_weight = 0;
                        $total_amount = 0;
                    @endphp
                    @forelse( $weightBasedReportData as $sale)
                        <tr>
                            <td>{{ $sale->item_code }}</td>
                            <td>{{ $sale->item_name }}</td>
                            <td>{{ $sale->packs }}</td>
                            <td>{{ number_format($sale->weight, 2) }}</td>
                            <td>{{ number_format($sale->total, 2) }}</td>
                        </tr>
                        @php
                            $total_packs += $sale->packs;
                            $total_weight += $sale->weight;
                            $total_amount += $sale->total;
                        @endphp
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-white bg-secondary">වාර්තා නැත</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="table-secondary fw-bold">
                        <td class="text-end" colspan="2">මුළු එකතුව:</td>
                        <td>{{ $total_packs }}</td>
                        <td>{{ number_format($total_weight, 2) }}</td>
                        <td>{{ number_format($total_amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>

           
        </div>
    </div>
 {{-- Section 5 - Bill Summary (Sales By Bill) --}}
        <div class="custom-card mt-4">
            <div class="report-title-bar">
                <h2 class="company-name">Sales Report</h2>
                <h4 class="fw-bold text-white">Bill Summary</h4>
               
            </div>
            <div class="card-body p-0">
                @if ($salesByBill->isEmpty())
                    <div class="alert alert-info m-3">No sales records found.</div>
                @else
                    @php $grandTotal = 0; @endphp
                    @foreach ($salesByBill as $billNo => $sales)
                        @php
                            $firstPrinted = $sales->first()->FirstTimeBillPrintedOn ?? null;
                            $reprinted = $sales->first()->BillReprintedOn ?? null;
                            $billTotal = 0;
                        @endphp
                        <div class="mb-4 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold text-white mb-2">Bill No: {{ $billNo }}</h5>
                            <div class="right-info">
                                @if($firstPrinted)
                                    <span>First Printed: {{ \Carbon\Carbon::parse($firstPrinted)->format('Y-m-d H:i') }}</span>
                                @endif
                                @if($reprinted)
                                    <span>Reprinted: {{ \Carbon\Carbon::parse($reprinted)->format('Y-m-d H:i') }}</span>
                                @endif
                            </div>
                        </div>
                        <table class="table table-bordered table-striped table-hover table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>කේතය</th>
                                    <th>පාරිභෝගික කේතය</th>
                                    <th>සැපයුම්කරු කේතය</th>
                                    <th>භාණ්ඩ නාමය</th>
                                    <th>බර</th>
                                    <th>කිලෝවකට මිල</th>
                                    <th>එකතුව</th>
                                    <th>පැකේජ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sales as $sale)
                                    @php $billTotal += $sale->total; @endphp
                                    <tr>
                                        <td>{{ $sale->code }}</td>
                                        <td>{{ $sale->customer_code }}</td>
                                        <td>{{ $sale->supplier_code }}</td>
                                        <td>{{ $sale->item_name }}</td>
                                        <td>{{ $sale->weight }}</td>
                                        <td>{{ number_format($sale->price_per_kg, 2) }}</td>
                                        <td>{{ number_format($sale->total, 2) }}</td>
                                        <td>{{ $sale->packs }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="6" class="text-end">Bill Total:</th>
                                    <th colspan="2">{{ number_format($billTotal, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                        @php $grandTotal += $billTotal; @endphp
                    @endforeach
                    <div class="text-end fw-bold text-white mt-4 me-3">
                        <h3>Grand Total: {{ number_format($grandTotal, 2) }}</h3>
                    </div>
                @endif
            </div>
    {{-- Section 6 - 📦 වෙනස් කිරීම (Changes Report) --}}
<div class="card-header text-center">
    <div class="report-title-bar">
        <div>
            <h2 class="company-name">TGK ට්‍රේඩර්ස්</h2>
            <h4 class="fw-bold text-white">📦 වෙනස් කිරීම</h4>
        </div>
        <div>
            <span class="right-info">{{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}</span><br>
            <button class="print-btn" onclick="window.print()">🖨️ මුද්‍රණය</button>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover table-sm align-middle text-center" style="font-size: 14px;">
        <thead class="table-dark">
            <tr>
                <th>විකුණුම්කරු</th>
                <th>මලු</th>
                <th>වර්ගය</th>
                <th>බර</th>
                <th>මිල</th>
                <th>මුළු මුදල</th>
                <th>බිල්පත් අංකය</th>
                <th>පාරිභෝගික කේතය</th>
                <th>වර්ගය (type)</th>
                <th>දිනය සහ වේලාව</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Group sales adjustments by code
                $salesadjustments = $salesadjustments->groupBy('code');
            @endphp

            @forelse ($salesadjustments as $code => $group)
                @php
                    $original = $group->firstWhere('type', 'original');
                    $updated = $group->firstWhere('type', 'updated');
                    $deleted = $group->firstWhere('type', 'deleted');
                @endphp

                {{-- Original --}}
                @if ($original)
                    <tr class="table-success">
                        <td>{{ $original->code }}</td>
                        <td>{{ $original->packs }}</td>
                        <td>{{ $original->item_name }}</td>
                        <td>{{ $original->weight }}</td>
                        <td>{{ number_format($original->price_per_kg, 2) }}</td>
                        <td>{{ number_format($original->total, 2) }}</td>
                        <td>{{ $original->bill_no }}</td>
                        <td>{{ $original->customer_code }}</td>
                        <td>{{ $original->type }}</td>
                        <td>{{ $original->created_at->timezone('Asia/Colombo')->format('Y-m-d H:i') }}</td>
                    </tr>
                @endif

                {{-- Updated --}}
                @if ($updated)
                    <tr class="table-warning">
                        <td>{{ $updated->code }}</td>
                        <td class="{{ $original && $updated->packs != $original->packs ? 'changed' : '' }}">{{ $updated->packs }}</td>
                        <td class="{{ $original && $updated->item_name != $original->item_name ? 'changed' : '' }}">{{ $updated->item_name }}</td>
                        <td class="{{ $original && $updated->weight != $original->weight ? 'changed' : '' }}">{{ $updated->weight }}</td>
                        <td class="{{ $original && $updated->price_per_kg != $original->price_per_kg ? 'changed' : '' }}">{{ number_format($updated->price_per_kg, 2) }}</td>
                        <td class="{{ $original && $updated->total != $original->total ? 'changed' : '' }}">{{ number_format($updated->total, 2) }}</td>
                        <td>{{ $updated->bill_no }}</td>
                        <td class="{{ $original && $updated->customer_code != $original->customer_code ? 'changed' : '' }}">{{ $updated->customer_code }}</td>
                        <td>{{ $updated->type }}</td>
                        <td>{{ $updated->created_at->timezone('Asia/Colombo')->format('Y-m-d H:i') }}</td>
                    </tr>
                @endif

                {{-- Deleted --}}
                @if ($deleted)
                    <tr class="table-danger">
                        <td>{{ $deleted->code }}</td>
                        <td class="{{ $original && $deleted->packs != $original->packs ? 'changed' : '' }}">{{ $deleted->packs }}</td>
                        <td class="{{ $original && $deleted->item_name != $original->item_name ? 'changed' : '' }}">{{ $deleted->item_name }}</td>
                        <td class="{{ $original && $deleted->weight != $original->weight ? 'changed' : '' }}">{{ $deleted->weight }}</td>
                        <td class="{{ $original && $deleted->price_per_kg != $original->price_per_kg ? 'changed' : '' }}">{{ number_format($deleted->price_per_kg, 2) }}</td>
                        <td class="{{ $original && $deleted->total != $original->total ? 'changed' : '' }}">{{ number_format($deleted->total, 2) }}</td>
                        <td>{{ $deleted->bill_no }}</td>
                        <td class="{{ $original && $deleted->customer_code != $original->customer_code ? 'changed' : '' }}">{{ $deleted->customer_code }}</td>
                        <td>{{ $deleted->type }}</td>
                        <td>{{ $deleted->created_at->timezone('Asia/Colombo')->format('Y-m-d H:i') }}</td>
                    </tr>
                @endif

            @empty
                <tr>
                    <td colspan="10" class="text-center">සටහන් කිසිවක් සොයාගෙන නොමැත</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
 {{-- Section 7 - Financial Report --}}
        <div class="custom-card mt-4">
            <div class="report-title-bar">
                <div>
                    <h2 class="company-name">TGK ට්‍රේඩර්ස්</h2>
                    <h4 class="fw-bold text-white">මුදල් වාර්තාව</h4>
                </div>
                <div>
                    <span class="right-info">{{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}</span>
                  
                </div>
            </div>
            <div class="alert alert-info fw-bold">
                Sales Total: {{ number_format($financialTotalCr, 2) }}
            </div>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>විස්තරය</th>
                        <th>ලැබීම්</th>
                        <th>ගෙවීම</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($financialReportData as $row)
                        <tr>
                            <td>{{ $row['description'] }}</td>
                            <td>{{ $row['dr'] ? number_format($row['dr'], 2) : '' }}</td>
                            <td>{{ $row['cr'] ? number_format($row['cr'], 2) : '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td>Total</td>
                        <td>{{ number_format($financialTotalDr, 2) }}</td>
                        <td>{{ number_format($financialTotalCr, 2) }}</td>
                    </tr>
                    <tr class="fw-bold table-warning">
                        <td>ඇතැති මුදල්</td>
                        <td colspan="2">
                            @php $diff = $financialTotalCr - $financialTotalDr; @endphp
                            @if($diff < 0)
                                <span class="text-danger">{{ number_format($diff, 2) }}</span>
                            @else
                                <span class="text-success">{{ number_format($diff, 2) }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr class="fw-bold table-warning">
                        <td>💰 Profit</td>
                        <td colspan="2" class="text-success">{{ number_format($profitTotal, 2) }}</td>
                    </tr>
                    <tr class="fw-bold table-warning">
                        <td>Total Damages</td>
                        <td colspan="2" class="text-danger">{{ number_format($totalDamages, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>
  {{-- Section 8 - Loan/Customer Report --}}
    <div class="custom-card mt-4">
        <div class="report-title-bar">
            <h2 class="company-name">TGK ට්‍රේඩර්ස්</h2>
            <h4 class="fw-bold text-white">නයා වාර්තාව</h4>
            <span class="right-info">{{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}</span>
           
        </div>

        <div class="card-body p-0">
            @if ($errors->any())
                <div class="alert alert-danger m-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($loans->isEmpty())
                <div class="alert alert-info m-3">No loan records found for the selected filters.</div>
            @else
                  <table class="table table-bordered table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>පාරිභෝගික නම</th>
                                <th>බිල් අංකය</th>
                                <th>දිනය</th>
                                <th>විස්තරය</th>
                                <th>චෙක්පත්</th>
                                <th>බැංකුව</th>
                                <th>ලබීම්</th>
                                <th>දීම්</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $receivedTotal = 0;
                                $paidTotal = 0;
                            @endphp
                            @foreach ($loans as $loan)
                                @php
                                    // Determine if the amount is a receipt or a payment based on the description
                                    if ($loan->description === 'වෙළෙන්දාගේ ලාද පරණ නය') {
                                        $receivedTotal += $loan->amount;
                                        $receivedAmount = number_format($loan->amount, 2);
                                        $paidAmount = '';
                                    } elseif ($loan->description === 'වෙළෙන්දාගේ අද දින නය ගැනීම') {
                                        $paidTotal += $loan->amount;
                                        $receivedAmount = '';
                                        $paidAmount = number_format($loan->amount, 2);
                                    } else {
                                        $receivedAmount = '';
                                        $paidAmount = '';
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $loan->customer_short_name }}</td>
                                    <td>{{ $loan->bill_no }}</td>
                                    <td>{{ $loan->created_at->format('Y-m-d') }}</td>
                                    <td>{{ $loan->description }}</td>
                                    <td>{{ $loan->cheque_no }}</td>
                                    <td>{{ $loan->bank }}</td>
                                    <td>{{ $receivedAmount }}</td>
                                    <td>{{ $paidAmount }}</td>
                                </tr>
                            @endforeach
                            <!-- Total Row -->
                            <tr style="font-weight: bold; background-color: #dff0d8; color: black;">
                                <td colspan="6" class="text-end">එකතුව:</td>
                                <td>{{ number_format($receivedTotal, 2) }}</td>
                                <td>{{ number_format($paidTotal, 2) }}</td>
                            </tr>
                            <!-- Net Balance Row -->
                            <tr style="font-weight: bold; background-color: #004d00; color: white;">
                                @php
                                    $netBalance = $paidTotal - $receivedTotal;
                                @endphp
                                <td colspan="7" class="text-end">ශුද්ධ ශේෂය:</td>
                                <td>{{ number_format($netBalance, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
            @endif
        </div>
    </div>

</div>

</body>
</html>


   