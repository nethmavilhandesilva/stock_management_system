<!DOCTYPE html>
<html>
<head>
    <title>Sales Adjustment Report</title>
    <style>
        /* This is basic styling, you might need to adjust it for mPDF */
        body {  font-family: 'notosanssinhala', sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .original { background-color: #d4edda; }
        .updated { background-color: #fff3cd; }
        .deleted { background-color: #f8d7da; }
    </style>
</head>
<body>
    <h1>TGK ට්‍රේඩර්ස් - වෙනස් කිරීම් වාර්තාව</h1>
    <table>
        <thead>
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
            @foreach ($grouped as $group)
                @php
                    $original = $group->firstWhere('type', 'original');
                    $updated = $group->firstWhere('type', 'updated');
                    $deleted = $group->firstWhere('type', 'deleted');
                @endphp
                @if ($original)
                    <tr class="original">
                        <td>{{ $original->code }}</td>
                        <td>{{ $original->packs }}</td>
                        <td>{{ $original->item_name }}</td>
                        <td>{{ $original->weight }}</td>
                        <td>{{ number_format($original->price_per_kg, 2) }}</td>
                        <td>{{ number_format($original->total, 2) }}</td>
                        <td>{{ $original->bill_no }}</td>
                        <td>{{ strtoupper($original->customer_code) }}</td>
                        <td>{{ $original->type }}</td>
                        <td>{{ $original->created_at->timezone('Asia/Colombo')->format('Y-m-d H:i') }}</td>
                    </tr>
                @endif
                @if ($updated)
                    <tr class="updated">
                        <td>{{ $updated->code }}</td>
                        <td>{{ $updated->packs }}</td>
                        <td>{{ $updated->item_name }}</td>
                        <td>{{ $updated->weight }}</td>
                        <td>{{ number_format($updated->price_per_kg, 2) }}</td>
                        <td>{{ number_format($updated->total, 2) }}</td>
                        <td>{{ $updated->bill_no }}</td>
                        <td>{{ strtoupper($updated->customer_code) }}</td>
                        <td>{{ $updated->type }}</td>
                        <td>{{ $updated->created_at->timezone('Asia/Colombo')->format('Y-m-d H:i') }}</td>
                    </tr>
                @endif
                @if ($deleted)
                    <tr class="deleted">
                        <td>{{ $deleted->code }}</td>
                        <td>{{ $deleted->packs }}</td>
                        <td>{{ $deleted->item_name }}</td>
                        <td>{{ $deleted->weight }}</td>
                        <td>{{ number_format($deleted->price_per_kg, 2) }}</td>
                        <td>{{ number_format($deleted->total, 2) }}</td>
                        <td>{{ $deleted->bill_no }}</td>
                        <td>{{ strtoupper($deleted->customer_code) }}</td>
                        <td>{{ $deleted->type }}</td>
                        <td>{{ $deleted->created_at->timezone('Asia/Colombo')->format('Y-m-d H:i') }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</body>
</html>