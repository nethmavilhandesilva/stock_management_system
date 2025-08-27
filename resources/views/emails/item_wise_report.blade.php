<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item-Wise Report - TGK Traders</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .email-container {
            max-width: 900px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 6px;
            border: 1px solid #ddd;
            overflow: hidden;
        }
        .report-header {
            background-color: #006400;
            color: white;
            padding: 15px;
            text-align: center;
        }
        .report-header h2 {
            margin: 0;
            font-size: 22px;
        }
        .report-header h4 {
            margin: 5px 0 0;
            font-size: 16px;
            font-weight: bold;
        }
        .report-header .date-info {
            font-size: 14px;
            margin-top: 5px;
        }
        .content {
            padding: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 14px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #004d00;
            color: white;
        }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .total-row {
            background-color: #004d00;
            font-weight: bold;
            color: white;
        }
        .text-end { text-align: right; }
        .no-records {
            text-align: center;
            color: #777;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="report-header">
            <h2>TGK ට්‍රේඩර්ස්</h2>
            <h4>📦 අයිතමය අනුව වාර්තාව (Item-Wise Report)</h4>
            <div class="date-info">{{ \Carbon\Carbon::parse($settingDate)->format('Y-m-d') }}</div>
        </div>

        <div class="content">
            @if ($sales->isEmpty())
                <p class="no-records">No sales records found.</p>
            @else
             <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 14px;">
    <thead>
        <tr style="background-color: #f2f2f2; text-align: center;">
            <th style="border: 1px solid #ddd; padding: 8px;">බිල් අංකය</th>
            <th style="border: 1px solid #ddd; padding: 8px;">භාණ්ඩ නාමය</th>
            <th style="border: 1px solid #ddd; padding: 8px;">භාණ්ඩ කේතය</th>
            <th style="border: 1px solid #ddd; padding: 8px;">මලු</th>
            <th style="border: 1px solid #ddd; padding: 8px;">බර</th>
            <th style="border: 1px solid #ddd; padding: 8px;">මිල</th>
            <th style="border: 1px solid #ddd; padding: 8px;">එකතුව</th>
            <th style="border: 1px solid #ddd; padding: 8px;">ගෙණුම්කරු</th>
            <th style="border: 1px solid #ddd; padding: 8px;">GRN අංකය</th>
        </tr>
    </thead>
    <tbody>
        @foreach($sales as $sale)
        <tr>
            <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{ $sale->bill_no }}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">{{ $sale->item_name }}</td>
            <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{ $sale->item_code }}</td>
            <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ $sale->packs }}</td>
            <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ number_format($sale->weight, 2) }}</td>
            <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ number_format($sale->price_per_kg, 2) }}</td>
            <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ number_format($sale->total, 2) }}</td>
            <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{ $sale->customer_code }}</td>
            <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{ $sale->code }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="background-color: #f9f9f9; font-weight: bold;">
            <td colspan="3" style="border: 1px solid #ddd; padding: 8px; text-align: right;">මුළු එකතුව (Grand Total):</td>
            <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ $total_packs }}</td>
            <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ number_format($total_weight, 2) }}</td>
            <td style="border: 1px solid #ddd; padding: 8px;"></td>
            <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ number_format($total_amount, 2) }}</td>
            <td colspan="2" style="border: 1px solid #ddd; padding: 8px;"></td>
        </tr>
    </tfoot>
</table>

            @endif
        </div>
    </div>
</body>
</html>
