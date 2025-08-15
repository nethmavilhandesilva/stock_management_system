@extends('layouts.app')

@section('content')
<style>
    body {
        background-color: #99ff99 !important;
        font-size: 0.9rem; /* Smaller base font */
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
    @foreach($groupedData as $code => $data)
        <div class="card">
            <h2>
                Code: {{ $code }} 
                <span class="small-text">(Purchase Price: {{ number_format($data['purchase_price'], 2) }})</span>
            </h2>

            <h4>Sales Data</h4>
            <table class="table table-bordered table-sm text-white mb-2">
                <thead>
                    <tr>
                        <th>කේතය</th>
                        <th>ගෙණුම්කරු</th>
                        <th>අයිතම</th>
                        <th>සැපයුම්කරු</th>
                        <th>බර</th>
                        <th>කිලෝමිල</th>
                        <th>මුළු මුදල</th>
                        <th>පැක්</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['sales'] as $sale)
                        <tr>
                            <td>{{ $sale->code }}</td>
                            <td>{{ $sale->customer_code }}</td>
                            <td>{{ $sale->item_code }}</td>
                            <td>{{ $sale->supplier_code }}</td>
                            <td>{{ $sale->weight }}</td>
                            <td>{{ $sale->price_per_kg }}</td>
                            <td>{{ $sale->total }}</td>
                            <td>{{ $sale->packs }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <h4>Damage Section</h4>
            <table class="table table-bordered table-sm text-white mb-2">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Wasted Packs</th>
                        <th>Wasted Weight</th>
                        <th>Damage Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $code }}</td>
                        <td>{{ $data['damage']['wasted_packs'] }}</td>
                        <td>{{ $data['damage']['wasted_weight'] }}</td>
                        <td>{{ number_format($data['damage']['damage_value'], 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <h4>Profit</h4>
            <p class="{{ $data['profit'] >= 0 ? 'profit-positive' : 'profit-negative' }}">
                Profit: {{ number_format($data['profit'], 2) }}
            </p>
        </div>
    @endforeach
</div>
@endsection
