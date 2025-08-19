@extends('layouts.app')

@section('content')
    <style>
        body {
            background-color: #99ff99 !important;
            font-size: 0.9rem;
            /* Smaller base font */
        }

        .card {
            background-color: #006400;
            color: white;
            padding: 15px;
            margin-bottom: 15px;
            font-size: 0.85rem;
        }

        h2 {
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        h4 {
            font-size: 1rem;
            margin-top: 15px;
            margin-bottom: 8px;
        }

        table {
            font-size: 0.85rem;
        }

        .profit-positive {
            color: #00ff00;
            font-weight: bold;
            font-size: 0.95rem;
        }

        .profit-negative {
            color: #ff4500;
            font-weight: bold;
            font-size: 0.95rem;
        }

        .small-text {
            font-size: 0.85rem;
            color: #ffd700;
        }
    </style>

    <div style="background-color: #99ff99; min-height: 100vh; padding: 20px;">

        {{-- Per-Code Cards --}}
        @foreach($groupedData as $code => $data)
                <div class="card">
                    <h2>
                        Code: {{ $code }}
                        <span class="small-text">
                            (Item: {{ $data['sales']->first()->item_name ?? 'N/A' }},
                            Purchase Price: {{ number_format($data['purchase_price'], 2) }})
                        </span>
                    </h2>


                    <h4>Sales Data</h4>
                    <table class="table table-bordered table-sm text-white mb-2">
                        <thead>
                            <tr>
                                <th>දිනය</th>
                                <th>ගෙණුම්කරු</th>
                                <th>බර</th>
                                <th>කිලෝමිල</th>
                                <th>මුළු මුදල</th>
                                <th>පැක්</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['sales'] as $sale)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($sale->created_at)->format('Y-m-d') }}</td>
                                    <td>{{ $sale->customer_code }}</td>
                                    <td>{{ $sale->weight }}</td>
                                    <td>{{ $sale->price_per_kg }}</td>
                                    <td>{{ $sale->total }}</td>
                                    <td>{{ $sale->packs }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>



                    <h4>Purcases and Sales</h4>
                    <table class="table table-striped table-hover table-bordered">
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
                                $grandOriginalWeight = 0;
                                $grandOriginalPacks = 0;
                                $grandSoldWeight = 0;
                                $grandSoldPacks = 0;
                                $grandSalesValue = 0;
                                $grandRemainingWeight = 0;
                                $grandRemainingPacks = 0;
                            @endphp

                            @foreach($reportData->groupBy('grn_code') as $grnCode => $items)
                                @foreach($items as $item)
                                    @php
                                        $grandOriginalWeight += $item['original_weight'];
                                        $grandOriginalPacks += $item['original_packs'];
                                        $grandSoldWeight += $item['sold_weight'];
                                        $grandSoldPacks += $item['sold_packs'];
                                        $grandSalesValue += $item['total_sales_value'];
                                        $grandRemainingWeight += $item['remaining_weight'];
                                        $grandRemainingPacks += $item['remaining_packs'];
                                    @endphp
                                    <tr>
                                        <td>{{ $item['item_name'] }} ({{ $grnCode }})</td>
                                        <td>{{ number_format($item['original_weight'], 2) }}</td>
                                        <td>{{ number_format($item['original_packs']) }}</td>
                                        <td>{{ number_format($item['sold_weight'], 2) }}</td>
                                        <td>{{ number_format($item['sold_packs']) }}</td>
                                        <td>Rs. {{ number_format($item['total_sales_value'], 2) }}</td>
                                        <td>{{ number_format($item['remaining_weight'], 2) }}</td>
                                        <td>{{ number_format($item['remaining_packs']) }}</td>
                                    </tr>
                                @endforeach
                            @endforeach

                            <tr class="total-row">
                                <td class="text-end"><strong>සමස්ත එකතුව:</strong></td>
                                <td>{{ number_format($grandOriginalWeight, 2) }}</td>
                                <td>{{ number_format($grandOriginalPacks) }}</td>
                                <td>{{ number_format($grandSoldWeight, 2) }}</td>
                                <td>{{ number_format($grandSoldPacks) }}</td>
                                <td>Rs. {{ number_format($grandSalesValue, 2) }}</td>
                                <td>{{ number_format($grandRemainingWeight, 2) }}</td>
                                <td>{{ number_format($grandRemainingPacks) }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <h4>Damage Section</h4>
                    <table class="table table-bordered table-sm text-white mb-2">
                        <thead>
                            <tr>
                                <th>දිනය</th>
                                <th>Wasted Packs</th>
                                <th>Wasted Weight</th>
                                <th>Damage Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($data['updated_at'])->format('Y-m-d') }}</td>
                                <td>{{ $data['damage']['wasted_packs'] }}</td>
                                <td>{{ $data['damage']['wasted_weight'] }}</td>
                                <td>{{ number_format($data['damage']['damage_value'], 2) }}</td>
                            </tr>
                        </tbody>
                    </table>


                    <p class="{{ $data['profit'] >= 0 ? 'profit-positive' : 'profit-negative' }}">
                        Profit: {{ number_format($data['profit'], 2) }}
                    </p>

                </div>

            </div>

        @endforeach


@endsection