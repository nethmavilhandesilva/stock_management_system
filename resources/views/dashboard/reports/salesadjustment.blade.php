@extends('layouts.app')

@section('content')
<style>
    body { background-color: #99ff99 !important; }
    .report-title-bar { display: flex; justify-content: space-between; align-items: center; color: white; padding: 10px; }
    .company-name { font-size: 24px; font-weight: bold; }
    .print-btn { background-color: white; color: #004d00; border: none; padding: 6px 12px; border-radius: 4px; font-weight: bold; cursor: pointer; }
    .print-btn:hover { background-color: #e6e6e6; }
    .card-header { background-color: #004d00 !important; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
    h4.fw-bold { margin: 0; }
    table th, table td { text-align: center; vertical-align: middle; }
    .changed { color: red !important; font-weight: bold; }

    /* ================= PRINT STYLES ================= */
    @media print {
        /* Hide everything except container */
        body * {
            visibility: hidden;
        }
        .container, .container * {
            visibility: visible;
        }

        /* Position the container at top-left and shrink slightly */
        .container {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            transform: scale(0.9); /* Shrinks the content for A4 */
            transform-origin: top left;
        }

        /* Remove scrollbars */
        html, body {
            overflow: visible !important;
            height: auto !important;
        }

        /* Scale table & text to fit A4 */
        table {
            font-size: 11px !important; /* Smaller font for print */
            page-break-inside: avoid;
            width: 100% !important;
        }

        th, td {
            padding: 4px !important; /* Smaller cell padding */
        }

        .card-header {
            padding: 8px !important;
            font-size: 13px !important;
        }

        /* Hide buttons/links in print */
        .print-btn, a {
            display: none !important;
        }
    }
</style>

@php
    use Carbon\Carbon;

    if (!function_exists('formatDate')) {
        function formatDate($date) {
            return $date ? Carbon::parse($date)->timezone('Asia/Colombo')->format('Y-m-d H:i') : '-';
        }
    }

    $grouped = $entries->groupBy('code');
@endphp

<div class="container mt-4">
    <div class="card-header text-center">
        <div class="report-title-bar">
            <div>
                <h2 class="company-name">TGK ට්‍රේඩර්ස්</h2>
                <h4 class="fw-bold text-white">📦 වෙනස් කිරීම</h4>
            </div>
            <div>
                @php
                    $settingDate = \App\Models\Setting::value('value');
                @endphp
                <span class="right-info">{{ \Carbon\Carbon::parse($settingDate)->format('Y-m-d') }}</span><br>
                <button class="print-btn" onclick="window.print()">🖨️ මුද්‍රණය</button>
                <a href="{{ route('sales-adjustment.export.excel', request()->all()) }}" class="print-btn">📥 Excel</a>
                <a href="{{ route('sales-adjustment.export.pdf', request()->all()) }}" class="print-btn" style="background-color: #f44336; color: white;">📥 PDF</a>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover table-sm align-middle text-center" style="font-size: 14px;">
            <thead class="table-dark">
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
                @forelse ($grouped as $code => $group)
                    @php
                        $original = $group->firstWhere('type', 'original');
                        $updated = $group->firstWhere('type', 'updated');
                        $deleted = $group->firstWhere('type', 'deleted');
                    @endphp

                    {{-- Original Row --}}
                    @if ($original)
                        <tr class="table-success">
                            <td>{{ $original->code }}</td>
                            <td>{{ $original->packs }}</td>
                            <td>{{ $original->item_name }}</td>
                            <td>{{ $original->weight }}</td>
                            <td>{{ number_format($original->price_per_kg, 2) }}</td>
                            <td>{{ number_format($original->total, 2) }}</td>
                            <td>{{ $original->bill_no }}</td>
                            <td>{{ strtoupper($original->customer_code) }}</td>
                            <td>{{ $original->type }}</td>
                              <td>{{ $original->original_created_at->timezone('Asia/Colombo')->format('Y-m-d') }}</td>
                        </tr>
                    @endif

                    {{-- Updated Row --}}
                    @if ($updated)
                        <tr class="table-warning">
                            <td>{{ $updated->code }}</td>
                            <td class="{{ $original && $updated->packs != $original->packs ? 'changed' : '' }}">{{ $updated->packs }}</td>
                            <td class="{{ $original && $updated->item_name != $original->item_name ? 'changed' : '' }}">{{ $updated->item_name }}</td>
                            <td class="{{ $original && $updated->weight != $original->weight ? 'changed' : '' }}">{{ $updated->weight }}</td>
                            <td class="{{ $original && $updated->price_per_kg != $original->price_per_kg ? 'changed' : '' }}">{{ number_format($updated->price_per_kg, 2) }}</td>
                            <td class="{{ $original && $updated->total != $original->total ? 'changed' : '' }}">{{ number_format($updated->total, 2) }}</td>
                            <td>{{ $updated->bill_no }}</td>
                            <td class="{{ $original && $updated->customer_code != $original->customer_code ? 'changed' : '' }}">{{ strtoupper($updated->customer_code) }}</td>
                            <td>{{ $updated->type }}</td>
                           <td>
    {{ $updated->Date }} 
    {{ \Carbon\Carbon::parse($updated->created_at)->setTimezone('Asia/Colombo')->format('H:i:s') }}
</td>


                        </tr>
                    @endif

                    {{-- Deleted Row --}}
                    @if ($deleted)
                        <tr class="table-danger">
                            <td>{{ $deleted->code }}</td>
                            <td class="{{ $original && $deleted->packs != $original->packs ? 'changed' : '' }}">{{ $deleted->packs }}</td>
                            <td class="{{ $original && $deleted->item_name != $original->item_name ? 'changed' : '' }}">{{ $deleted->item_name }}</td>
                            <td class="{{ $original && $deleted->weight != $original->weight ? 'changed' : '' }}">{{ $deleted->weight }}</td>
                            <td class="{{ $original && $deleted->price_per_kg != $original->price_per_kg ? 'changed' : '' }}">{{ number_format($deleted->price_per_kg, 2) }}</td>
                            <td class="{{ $original && $deleted->total != $original->total ? 'changed' : '' }}">{{ number_format($deleted->total, 2) }}</td>
                            <td>{{ $deleted->bill_no }}</td>
                            <td class="{{ $original && $deleted->customer_code != $original->customer_code ? 'changed' : '' }}">{{ strtoupper($deleted->customer_code) }}</td>
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

    <div class="d-flex justify-content-center">
        {{ $entries->links() }}
    </div>
</div>
@endsection


