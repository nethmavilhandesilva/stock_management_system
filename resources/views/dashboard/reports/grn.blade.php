@extends('layouts.app')

@section('content')
<style>
    body {
        background-color: #99ff99 !important; /* Full page background */
    }
    .card {
        background-color: #006400; /* Card background */
        color: white; /* Text color */
    }
    
</style>

<div style="background-color: #99ff99; min-height: 100vh; padding: 30px;">
    <div class="card" style="background-color: #006400; color: white; padding: 20px;">
        <h2>GRN Report {{ request('code') ? ' for Code: ' . request('code') : '(All Records)' }}</h2>

        <h4>Sales Data</h4>
        <table class="table table-bordered table-sm text-white">
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
                @foreach($salesData as $sale)
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
        <table class="table table-bordered table-sm text-white">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Wasted Packs</th>
                    <th>Wasted Weight</th>
                    <th>Damage Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach($damageData as $damage)
                    <tr>
                        <td>{{ $damage['code'] }}</td>
                        <td>{{ $damage['wasted_packs'] }}</td>
                        <td>{{ $damage['wasted_weight'] }}</td>
                        <td>{{ number_format($damage['damage_value'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
