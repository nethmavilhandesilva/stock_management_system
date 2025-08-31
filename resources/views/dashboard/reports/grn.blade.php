@extends('layouts.app')

@section('content')
    <style>
        body {
            background-color: #99ff99 !important;
            font-size: 0.9rem;
        }

        .card {
            background: linear-gradient(135deg, #004d26, #006400);
            color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        h2 {
            font-size: 1.3rem;
            margin-bottom: 12px;
        }

        h4 {
            font-size: 1rem;
            margin-top: 15px;
            margin-bottom: 8px;
        }

        .small-text {
            font-size: 0.85rem;
            color: #ffd700;
        }

        .table {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            overflow: hidden;
        }

        .table th {
            background: rgba(255, 255, 255, 0.1);
        }

        .profit-positive {
            color: #00ff00;
            font-weight: bold;
            font-size: 1rem;
        }

        .profit-negative {
            color: #ff6347;
            font-weight: bold;
            font-size: 1rem;
        }
    </style>
    <style>
    .move-up {
        margin-top: -30px; /* decrease this value to move more up */
    }
</style>

    <div style="min-height: 100vh; padding: 20px;"> {{-- ✅ removed background override --}}

        {{-- Per-Code Cards --}}
        @foreach($groupedData as $code => $data)
            <div class="card">
              @php
    $settingDate = \App\Models\Setting::value('value');
@endphp

<div style="text-align: right; font-weight: bold;">
    {{ \Carbon\Carbon::parse($settingDate)->format('Y-m-d') }}
</div>

              <h2 class="move-up">
                    Code: {{ $code }}
                    <span class="small-text d-block">
                        Item: {{ $data['sales']->first()->item_name ?? 'N/A' }} |
                        Purchase Price: {{ number_format($data['purchase_price'], 2) }} |
                        ow: {{ number_format($data['totalOriginalWeight'], 2) }} |
                        op: {{ number_format($data['totalOriginalPacks'], 2) }} |
                        BW: {{ $data['remaining_weight'] }}|
                        BP: {{ $data['remaining_packs'] }} |
                     
                    </span>
                </h2>

                {{-- Sales Data --}}
                <h4>Sales Data</h4>
                <table class="table table-bordered table-sm text-white mb-2">
                    <thead>
                        <tr>
                            <th>දිනය</th>
                            <th>බිල් අංකය</th>
                            <th>ගෙණුම්කරු</th>
                            <th>බර</th>
                            <th>කිලෝමිල</th>
                            <th>මුළු මුදල</th>
                            <th>පැක්</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalWeight = 0;
                            $totalAmount = 0;
                            $totalPacks = 0;
                        @endphp

                        @foreach($data['sales'] as $sale)
                            @php
                                $totalWeight += $sale->weight;
                                $totalAmount += $sale->total;
                                $totalPacks  += $sale->packs;
                            @endphp
                            <tr>
                                <td>{{ $sale->Date }}</td>
                                <td>{{ $sale->bill_no }}</td>
                                <td>{{ $sale->customer_code }}</td>
                                <td>{{ $sale->weight }}</td>
                                <td>{{ $sale->price_per_kg }}</td>
                                <td>{{ $sale->total }}</td>
                                <td>{{ $sale->packs }}</td>
                            </tr>
                        @endforeach

                        {{-- Totals Row --}}
                        <tr style="font-weight: bold;">
                            <td colspan="3" class="text-center">මුළු එකතුව</td>
                            <td>{{ number_format($totalWeight, 2) }}</td>
                            <td>-</td>
                            <td>{{ number_format($totalAmount, 2) }}</td>
                            <td>{{ number_format($totalPacks, 2) }}</td>
                        </tr>
                    </tbody>
                </table>

               {{-- Damage Section --}}
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
        @php
            $totalDamagePacks = 0;
            $totalDamageWeight = 0;
            $totalDamageValue = 0;
        @endphp

        @if(!empty($data['damage']))
            {{-- Handle single record --}}
            <tr>
                <td>{{ \Carbon\Carbon::parse($data['updated_at'])->format('Y-m-d') }}</td>
                <td>{{ $data['damage']['wasted_packs'] }}</td>
                <td>{{ $data['damage']['wasted_weight'] }}</td>
                <td>{{ number_format($data['damage']['damage_value'], 2) }}</td>
            </tr>

            @php
                $totalDamagePacks += $data['damage']['wasted_packs'];
                $totalDamageWeight += $data['damage']['wasted_weight'];
                $totalDamageValue += $data['damage']['damage_value'];
            @endphp
        @endif

        {{-- Totals Row --}}
        <tr style="font-weight: bold;">
            <td class="text-center">මුළු එකතුව</td>
            <td>{{ number_format($totalDamagePacks, 2) }}</td>
            <td>{{ number_format($totalDamageWeight, 2) }}</td>
            <td>{{ number_format($totalDamageValue, 2) }}</td>
        </tr>
    </tbody>
</table>


                {{-- Profit --}}
                <p class="{{ $data['profit'] >= 0 ? 'profit-positive' : 'profit-negative' }}">
                    Profit: {{ number_format($data['profit'], 2) }}
                </p>
            </div>
        @endforeach

    </div>
@endsection
