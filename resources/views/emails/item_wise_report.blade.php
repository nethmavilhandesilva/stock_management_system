<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item-Wise Report - TGK Traders</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; color: #333; }
        .email-container { max-width: 800px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); overflow: hidden; }
        .report-header { background-color: #006400; color: white; padding: 20px; text-align: center; }
        .report-header h2 { margin: 0; font-size: 24px; }
        .report-header h4 { margin: 5px 0 0; font-size: 18px; font-weight: bold; }
        .report-header .date-info { font-size: 14px; margin-top: 5px; }
        .content { padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #004d00; color: white; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .total-row { background-color: #004d00; font-weight: bold; color: white; text-align: right; }
        .text-end { text-align: right; }
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
                <p style="text-align: center; color: #777;">No sales records found for the selected filters.</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>බිල් අංකය (Bill No)</th>
                            <th>වර්ගය (Type)</th>
                            <th>භාණ්ඩ කේතය (Item Code)</th>
                            <th>මලු (Packs)</th>
                            <th>පැකට් (Packets)</th>
                            <th>බර (kg)</th>
                            <th>මිල (Rs/kg)</th>
                            <th>එකතුව (Rs)</th>
                            <th>ගෙණුම්කරු (Customer)</th>
                            <th>GRN අංකය (GRN No)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $sale)
                        <tr>
                            <td>{{ $sale->bill_no }}</td>
                            <td>{{ $sale->item_name }}</td>
                            <td>{{ $sale->item_code }}</td>
                            <td class="text-end">{{ $sale->packs }}</td>
                            <td class="text-end">{{ $sale->packs }}</td>
                            <td class="text-end">{{ number_format($sale->weight, 2) }}</td>
                            <td class="text-end">{{ number_format($sale->price_per_kg, 2) }}</td>
                            <td class="text-end">{{ number_format($sale->total, 2) }}</td>
                            <td>{{ $sale->customer_code }}</td>
                            <td>{{ $sale->code }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="3" class="text-end">මුළු එකතුව (Grand Total):</td>
                            <td class="text-end">{{ $total_packs }}</td>
                            <td class="text-end">{{ $total_packs }}</td>
                            <td class="text-end">{{ number_format($total_weight, 2) }}</td>
                            <td></td>
                            <td class="text-end">{{ number_format($total_amount, 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        </div>
    </div>
</body>
</html>
