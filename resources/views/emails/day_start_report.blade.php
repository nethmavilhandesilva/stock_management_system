<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Combined Daily Report</title>
    <style>
        /* Base Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #eef2f7;
            margin: 0;
            padding: 0;
            font-size: 14px;
            color: #333;
        }
        .container {
            width: 100%;
            max-width: 1100px;
            margin: 20px auto;
            padding: 0 15px;
        }

        /* Section Cards & Headers */
        .report-section {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
            overflow: hidden;
        }
        .report-header {
            background-color: #004d00;
            color: white;
            padding: 20px 25px;
            text-align: center;
            border-radius: 8px 8px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .report-header .title {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .report-header h2, .report-header h4 {
            margin: 0;
            line-height: 1.2;
            color: white;
        }
        .report-header h2 { font-size: 22px; }
        .report-header h4 { font-size: 16px; font-weight: normal; }
        .report-header .date-info {
            font-size: 13px;
            color: #ccc;
            text-align: right;
        }
        .print-btn {
            background-color: #003300;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        .print-btn:hover { background-color: #001a00; }

        /* Tables */
        .table-container { padding: 20px; overflow-x: auto; }
        .report-table, .compact-table, .bill-summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            color: #333;
            margin-top: 15px;
        }
        .report-table th, .report-table td,
        .compact-table th, .compact-table td,
        .bill-summary-table th, .bill-summary-table td {
            padding: 10px;
            border: 1px solid #e0e0e0;
            text-align: center;
            white-space: nowrap;
        }
        .report-table thead th, .report-table tfoot td {
            background-color: #003300;
            color: white;
        }
        .report-table tbody tr:nth-of-type(odd) { background-color: #f9f9f9; }
        .report-table tbody tr:hover { background-color: #e6f7ff; }

        .item-summary-row td { font-weight: bold; background-color: #e0e0e0 !important; }
        .total-row td { font-weight: bold; background-color: #008000 !important; color: white !important; }
        .total-row td:first-child { text-align: right; }

        .bill-details { padding: 20px; }
        .bill-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 10px;
            border-bottom: 2px solid #004d00;
            margin-bottom: 15px;
        }
        .bill-header h5 {
            margin: 0;
            color: #004d00;
            font-size: 16px;
        }
        .bill-header .info {
            font-size: 12px;
            color: #666;
            text-align: right;
        }
        .bill-total-row th {
            text-align: right !important;
            font-weight: bold;
        }
        .bill-total-row th:last-child {
            background-color: #e0e0e0;
            color: #333;
        }
        .grand-total {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
            padding: 15px;
            border-top: 3px solid #008000;
            color: #004d00;
            background-color: #f0f0f0;
        }

        /* Sales Adjustments Table */
        .sales-adjustments-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .sales-adjustments-table th, .sales-adjustments-table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .sales-adjustments-table thead th {
            background-color: #333;
            color: white;
        }
        .table-success { background-color: #d4edda; } /* Original */
        .table-warning { background-color: #fff3cd; } /* Updated */
        .table-danger { background-color: #f8d7da; } /* Deleted */
        .changed { background-color: #ffc107; font-weight: bold; color: #333; }

        /* Financial Report */
        .financial-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .financial-table th, .financial-table td {
            border: 1px solid #e0e0e0;
            padding: 10px;
        }
        .financial-table thead th {
            background-color: #004d00;
            color: white;
            font-weight: bold;
        }
        .financial-total-row { background-color: #f0f0f0; font-weight: bold; }
        .financial-net-balance-row, .financial-profit-row, .financial-damages-row {
            background-color: #eaf8ff;
            font-weight: bold;
        }
        .financial-net-balance-row td:last-child,
        .financial-profit-row td:last-child,
        .financial-damages-row td:last-child {
            font-size: 1.1em;
        }

        /* Loan Report */
        .loan-table th, .loan-table td {
            padding: 8px;
            text-align: left;
        }
        .loan-table th:last-child, .loan-table td:last-child { text-align: right; }
        .loan-totals-row {
            background-color: #dff0d8;
            font-weight: bold;
            color: black;
        }
        .loan-net-balance-row {
            background-color: #004d00;
            color: white;
            font-weight: bold;
        }

        /* Utility Classes */
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .text-muted { color: #6c757d; }
        .p-4 { padding: 1.5rem !important; }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .alert-info { background-color: #e2f4ff; color: #004d00; }
        .alert-danger { background-color: #f8d7da; color: #721c24; }
        .mb-4 { margin-bottom: 25px; }
        .mt-4 { margin-top: 25px; }

        /* Print Styles */
        @media print {
            body { background-color: #fff !important; }
            .report-section { box-shadow: none !important; border: 1px solid #eee; }
            .report-header { background-color: #eee !important; color: #000 !important; }
            .report-header h2, .report-header h4, .date-info { color: #000 !important; }
            .print-btn { display: none !important; }
            .report-table th, .report-table td,
            .compact-table th, .compact-table td,
            .bill-summary-table th, .bill-summary-table td,
            .sales-adjustments-table th, .sales-adjustments-table td,
            .financial-table th, .financial-table td,
            .loan-table th, .loan-table td { border-color: #ccc; }
            .report-table thead th, .report-table tfoot td { background-color: #ddd !important; color: #000 !important; }
            .total-row td { background-color: #e0e0e0 !important; color: #333 !important; }
            .changed { background-color: #ffc107 !important; color: #333 !important; }
        }
    </style>
  
</head>
<body>

    <div class="container">

        {{-- Section 1 - Combined Daily Report --}}
        <div class="report-section">
            <div class="report-header">
                <div class="title">
                    <h2 class="company-name">TGK ට්‍රේඩර්ස්</h2>
                    <h4>📦 විකුණුම්/බර මත්තෙහි ඉතිරි වාර්තාව</h4>
                </div>
                <div class="date-info">
                    <span>{{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}</span>
                   
                </div>
            </div>
            <div class="table-container">
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
                            <th>බර</th>
                            <th>මලු</th>
                            <th>බර</th>
                            <th>මලු</th>
                            <th>බර</th>
                            <th>මලු</th>              
                        </tr>
                    </thead>
                    <tbody>
                        @php
                           $grandTotalOriginalWeight = 0;
                            $grandTotalOriginalPacks = 0;
                            $grandTotalSoldWeight = 0;
                            $grandTotalSoldPacks = 0;
                          
                            $grandTotalSalesValue = 0;
                            $grandTotalRemainingWeight = 0;
                            $grandTotalRemainingPacks = 0;
                           
                        @endphp
                        @forelse($dayStartReportData as $item)
                            @php
                                $grandTotalOriginalWeight += $item['original_weight'];
                                $grandTotalOriginalPacks += $item['original_packs'];
                                 $grandTotalSoldWeight += $item['sold_weight'];
                                $grandTotalSoldPacks += $item['sold_packs'];
                              
                                $grandTotalSalesValue += $item['total_sales_value'];
                                $grandTotalRemainingWeight += $item['remaining_weight'];
                                $grandTotalRemainingPacks += $item['remaining_packs'];
                               
                            @endphp
                            <tr class="item-summary-row">
                                <td>{{ $item['item_name'] }} ({{ $item['grn_code'] }})</td>
                                <td>{{ number_format($item['original_weight'], 2) }}</td>
                                <td>{{ number_format($item['original_packs']) }}</td>
                                <td>{{ number_format($item['sold_weight'], 2) }}</td>
                                <td>{{ number_format($item['sold_packs']) }}</td>
                                
                                <td>Rs. {{ number_format($item['total_sales_value'], 2) }}</td>
                                 <td>{{ number_format($item['remaining_weight'], 2) }}</td>
                                <td>{{ number_format($item['remaining_packs']) }}</td>
                               
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">දත්ත නොමැත.</td>
                            </tr>
                        @endforelse
                        <tr class="total-row">
                            <td colspan="1">සමස්ත එකතුව:</td>
                             <td>{{ number_format($grandTotalOriginalWeight, 2) }}</td>
                            <td>{{ number_format($grandTotalOriginalPacks) }}</td>
                               <td>{{ number_format($grandTotalSoldWeight, 2) }}</td>
                            <td>{{ number_format($grandTotalSoldPacks) }}</td>
                         
                            <td>Rs. {{ number_format($grandTotalSalesValue, 2) }}</td>
                            <td>{{ number_format($grandTotalRemainingWeight, 2) }}</td>
                            <td>{{ number_format($grandTotalRemainingPacks) }}</td>
                            
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Section 2 - GRN Report --}}
<div class="report-section">
    <div class="report-header">
        <div class="title">
            <h2 class="company-name">TGK ට්‍රේඩර්ස්</h2>
            <h4>📦 විකුණුම්/බර මත්තෙහි ඉතිරි වාර්තාව (GRN)</h4>
        </div>
        <div class="date-info">
            <span>{{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}</span>
           
        </div>
    </div>
    <div class="table-container">
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
                    <th>බර</th>
                    <th>මලු</th>
                    <th>බර</th>
                    <th>මලු</th>
                    <th>බර</th>
                    <th>මලු</th>
                   
                </tr>
            </thead>
            <tbody>
                @php
                    $grnGrandTotalOriginalWeight = 0;
                    $grnGrandTotalOriginalPacks = 0;
                    $grnGrandTotalSoldWeight = 0;
                    $grnGrandTotalSoldPacks = 0;
                   
                    $grnGrandTotalSalesValue = 0;
                    $grnGrandTotalRemainingWeight = 0;
                    $grnGrandTotalRemainingPacks = 0;
                  
                @endphp
                @forelse($grnReportData as $item)
                    @php
                     $grnGrandTotalOriginalWeight += $item['original_weight'];
                        $grnGrandTotalOriginalPacks += $item['original_packs'];
                          $grnGrandTotalSoldWeight += $item['sold_weight'];
                        $grnGrandTotalSoldPacks += $item['sold_packs'];
                      
                        $grnGrandTotalSalesValue += $item['total_sales_value'];
                           $grnGrandTotalRemainingWeight += $item['remaining_weight'];
                        $grnGrandTotalRemainingPacks += $item['remaining_packs'];
                     
                    @endphp
                    <tr>
                        <td>{{ $item['item_name'] }}</td>
                         <td>{{ number_format($item['original_weight'], 2) }}</td>
                        <td>{{ number_format($item['original_packs']) }}</td>
                          <td>{{ number_format($item['sold_weight'], 2) }}</td>
                        <td>{{ number_format($item['sold_packs']) }}</td>
                      
                        <td>Rs. {{ number_format($item['total_sales_value'], 2) }}</td>
                         <td>{{ number_format($item['remaining_weight'], 2) }}</td>
                        <td>{{ number_format($item['remaining_packs']) }}</td>
                       
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">GRN දත්ත නොමැත.</td>
                    </tr>
                @endforelse
                <tr class="total-row">
                    <td>සමස්ත එකතුව:</td>
                     <td>{{ number_format($grnGrandTotalOriginalWeight, 2) }}</td>
                    <td>{{ number_format($grnGrandTotalOriginalPacks) }}</td>
                      <td>{{ number_format($grnGrandTotalSoldWeight, 2) }}</td>
                    <td>{{ number_format($grnGrandTotalSoldPacks) }}</td>
                  
                    <td>Rs. {{ number_format($grnGrandTotalSalesValue, 2) }}</td>
                      <td>{{ number_format($grnGrandTotalRemainingWeight, 2) }}</td>
                    <td>{{ number_format($grnGrandTotalRemainingPacks) }}</td>
                  
                </tr>
            </tbody>
        </table>
    </div>
</div>

        {{-- Section 4 - New Compact Sales by GRN/Filters --}}
        <div class="report-section">
            <div class="report-header">
                <div class="title">
                    <h2 class="company-name">TGK ට්‍රේඩර්ස්</h2>
                    <h4>මුළු අයිතම විකිණුම් – ප්‍රමාණ අනුව</h4>
                </div>
                <div class="date-info">
                    <span>{{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}</span>
                  
                </div>
            </div>
            <div class="table-container">
                <table class="report-table compact-table">
                    <thead>
                        <tr>
                            <th>අයිතම කේතය</th>
                            <th>වර්ගය</th>
                            <th>බර</th>
                            <th>මලු</th>
                         
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
                                <td>{{ number_format($sale->weight, 2) }}</td>
                                <td>{{ $sale->packs }}</td>
                            
                                <td>Rs. {{ number_format($sale->total, 2) }}</td>
                            </tr>
                            @php
                                $total_packs += $sale->packs;
                                $total_weight += $sale->weight;
                                $total_amount += $sale->total;
                            @endphp
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">වාර්තා නැත</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="2" class="text-end">මුළු එකතුව:</td>
                             <td>{{ number_format($total_weight, 2) }}</td>
                            <td>{{ $total_packs }}</td>
                           
                            <td>Rs. {{ number_format($total_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

      
        {{-- Section 6 - 📦 වෙනස් කිරීම (Changes Report) --}}
        <div class="report-section">
            <div class="report-header">
                <div class="title">
                    <h2 class="company-name">TGK ට්‍රේඩර්ස්</h2>
                    <h4>📦 වෙනස් කිරීම</h4>
                </div>
                <div class="date-info">
                    <span>{{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}</span>
                  
                </div>
            </div>
            <div class="table-container">
                <table class="sales-adjustments-table">
                    <thead>
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
                                    <td>{{ $original->original_created_at->timezone('Asia/Colombo')->format('Y-m-d') }}</td>
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
                                    <td>
    {{ $updated->Date }} 
    {{ \Carbon\Carbon::parse($updated->created_at)->setTimezone('Asia/Colombo')->format('H:i:s') }}
</td>
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
                                      <td>
    {{ $deleted->Date }} 
    {{ \Carbon\Carbon::parse($deleted->created_at)->setTimezone('Asia/Colombo')->format('H:i:s') }}
</td>
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
        </div>

      

<style>
    .custom-card {
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        background-color: #ffffff;
        overflow: hidden;
        margin: 20px auto;
        max-width: 800px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .report-title-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        background: linear-gradient(90deg, #4b79a1, #283e51);
        color: #fff;
        border-bottom: 2px solid #ccc;
    }

    .company-name {
        font-size: 1.8rem;
        font-weight: bold;
        margin: 0;
    }

    .fw-bold {
        margin: 0;
        font-size: 1.1rem;
    }

    .right-info {
        font-size: 0.95rem;
        background-color: rgba(255,255,255,0.2);
        padding: 3px 8px;
        border-radius: 5px;
    }

    .print-btn {
        background-color: #ff9800;
        color: #fff;
        border: none;
        padding: 6px 12px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 0.9rem;
        transition: background 0.3s;
    }

    .print-btn:hover {
        background-color: #e68900;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    thead th {
        font-size: 1rem;
        text-align: left;
        padding: 10px;
    }

    tbody td {
        padding: 10px;
        font-size: 0.95rem;
    }

    tfoot th, tfoot td {
        font-size: 1rem;
        font-weight: 600;
        padding: 10px;
    }

   
    
</style>




      
</body>
</html>

   