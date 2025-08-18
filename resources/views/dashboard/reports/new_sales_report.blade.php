@extends('layouts.app')

@section('content')
<style>
    body {
        background-color: #99ff99;
    }

    @media print {
        body * { visibility: hidden; }
        .custom-card, .custom-card * { visibility: visible; }
        .custom-card { position: absolute; left: 0; top: 0; width: 100%; }
        body, .custom-card { background-color: white !important; color: black !important; }
    }

    .blue-highlight { color: #64b5f6 !important; font-weight: bold; }
    .red-highlight { color: #ef5350 !important; font-weight: bold; }

    .custom-card { background-color: #006400 !important; color: white; padding: 1rem !important; }
    table.table { font-size: 0.9rem; }
    table.table td, table.table th { padding: 0.3rem 0.6rem !important; vertical-align: middle; }
    .custom-card table { background-color: #006400 !important; color: white; }
    .custom-card table thead, .custom-card table tfoot { background-color: #004d00 !important; color: white; }
    .custom-card table tbody tr:nth-child(odd) { background-color: #00550088; }
    .custom-card table tbody tr:nth-child(even) { background-color: transparent; }

    .report-title-bar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap; }
    .company-name { font-weight: 700; font-size: 1.5rem; color: white; margin: 0; }
    .report-title-bar h4 { margin: 0; color: white; font-weight: 700; white-space: nowrap; }
    .right-info { color: white; font-weight: 600; white-space: nowrap; font-size: 0.85rem; display: flex; flex-direction: column; text-align: right; gap: 2px; }

    .print-btn { background-color: #004d00; color: white; border: none; padding: 0.3rem 0.8rem; border-radius: 5px; cursor: pointer; font-weight: 600; white-space: nowrap; font-size: 0.9rem; transition: background-color 0.3s ease; }
    .print-btn:hover { background-color: #003300; }
</style>

<div class="container mt-2" style="background-color: #99ff99; min-height: 100vh; padding: 15px;">
    <div class="card custom-card shadow border-0 rounded-3 p-4">

        {{-- Report Header --}}
        <div class="report-title-bar">
            <h2 class="company-name">Sales Report</h2>
            <h4 class="fw-bold text-white">Bill Summary</h4>
            <button class="print-btn" onclick="window.print()">🖨️ Print</button>
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

                {{-- Grand Total --}}
                <div class="text-end fw-bold text-white mt-4 me-3">
                    <h3>Grand Total: {{ number_format($grandTotal, 2) }}</h3>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
