<div style="font-family: sans-serif; padding: 20px; border: 1px solid #ccc;">
    <h2 style="font-weight: bold;">TGK ට්‍රේඩර්ස්</h2>
    <h4>මුළු අයිතම විකිණුම් – ප්‍රමාණ අනුව</h4>
    <span style="font-size: 14px;">{{ $settingDate }}</span>

    <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
        <thead style="background-color: #4CAF50; color: white;">
            <tr>
                <th style="padding: 8px; border: 1px solid #ddd; text-align: left;">අයිතම කේතය</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align: left;">වර්ගය</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align: left;">මලු</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align: left;">බර</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align: left;">එකතුව</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_packs = 0;
                $total_weight = 0;
                $total_amount = 0;
            @endphp

            @foreach($sales as $sale)
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd;">{{ $sale->item_code }}</td>
                    <td style="padding: 8px; border: 1px solid #ddd;">{{ $sale->item_name }}</td>
                    <td style="padding: 8px; border: 1px solid #ddd;">{{ $sale->packs }}</td>
                    <td style="padding: 8px; border: 1px solid #ddd;">{{ number_format($sale->weight, 2) }}</td>
                    <td style="padding: 8px; border: 1px solid #ddd;">{{ number_format($sale->total, 2) }}</td>
                </tr>
                @php
                    $total_packs += $sale->packs;
                    $total_weight += $sale->weight;
                    $total_amount += $sale->total;
                @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td colspan="2" style="padding: 8px; border: 1px solid #ddd; text-align: right;">මුළු එකතුව:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $total_packs }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ number_format($total_weight, 2) }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ number_format($total_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
