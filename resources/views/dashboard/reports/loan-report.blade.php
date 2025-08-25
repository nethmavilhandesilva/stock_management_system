@extends('layouts.app')

@section('content')
<style>
    body {
        background-color: #99ff99;
    }

    /* ===== PRINT SETTINGS ===== */
    @media print {
        /* Hide everything except the card */
        body * {
            visibility: hidden;
        }

        .custom-card, .custom-card * {
            visibility: visible;
        }

        .custom-card {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

        /* Optional: Change background & text color for print */
        body, .custom-card {
            background-color: white !important;
            color: black !important;
        }
    }

    /* New CSS classes for highlighting */
    .blue-highlight {
        color: #64b5f6 !important; /* A slightly brighter blue for visibility */
        font-weight: bold;
    }

    .red-highlight {
        color: #ef5350 !important; /* A brighter red for visibility */
        font-weight: bold;
    }

    .custom-card {
        background-color: #006400 !important;
        color: white;
        padding: 1rem !important; /* slightly less padding */
    }

    table.table {
        font-size: 0.9rem; /* smaller font */
    }

    table.table td, table.table th {
        padding: 0.3rem 0.6rem !important; /* less padding inside cells */
        vertical-align: middle;
    }

    .custom-card table {
        background-color: #006400 !important; /* dark green */
        color: white;
    }

    .custom-card table thead, 
    .custom-card table tfoot {
        background-color: #004d00 !important;
        color: white;
    }

    .custom-card table tbody tr:nth-child(odd) {
        background-color: #00550088; /* slightly lighter translucent green */
    }

    .custom-card table tbody tr:nth-child(even) {
        background-color: transparent;
    }

    .report-title-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem; /* reduced gap */
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    .company-name {
        font-weight: 700;
        font-size: 1.5rem;
        color: white;
        margin: 0;
    }

    .report-title-bar h4 {
        margin: 0;
        color: white;
        font-weight: 700;
        white-space: nowrap;
    }

    .right-info {
        color: white;
        font-weight: 600;
        white-space: nowrap;
        font-size: 0.85rem; /* smaller font */
    }

    .print-btn {
        background-color: #004d00;
        color: white;
        border: none;
        padding: 0.3rem 0.8rem;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 600;
        white-space: nowrap;
        font-size: 0.9rem;
        transition: background-color 0.3s ease;
    }

    .print-btn:hover {
        background-color: #003300;
    }
</style>

<div class="container mt-2" style="background-color: #99ff99; min-height: 100vh; padding: 15px;">
    <div class="card custom-card shadow border-0 rounded-3 p-4">
        <div class="report-title-bar">
            <h2 class="company-name">TGK ට්‍රේඩර්ස්</h2>
            <h4 class="fw-bold text-white">ණය වාර්තාව</h4>
                        @php
    $settingDate = \App\Models\Setting::value('value');
@endphp

<span class="right-info">
    {{ \Carbon\Carbon::parse($settingDate)->format('Y-m-d') }}
</span>
            <button class="print-btn" onclick="window.print()">🖨️ මුද්‍රණය</button>
        </div>

        <div class="card-body p-0">
            @if ($loans->isEmpty())
                <div class="alert alert-info m-3">
                    No loan records found.
                </div>
            @else
              <table class="table table-bordered table-striped table-hover table-sm mb-0">
    <thead>
        <tr>
            <th>පාරිභෝගික නම</th>
            <th>මුදල</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($loans as $loan)
            <tr>
                <td class="{{ $loan->highlight_color ?? '' }}">{{ $loan->customer_short_name }}</td>
                <td>{{ number_format($loan->total_amount, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th class="text-end">Grand Total:</th>
            <th>
                {{
                    number_format($loans->sum(function($loan) {
                        return $loan->total_amount;
                    }), 2)
                }}
            </th>
        </tr>
    </tfoot>
</table>
            @endif
        </div>
    </div>
</div>
@endsection
