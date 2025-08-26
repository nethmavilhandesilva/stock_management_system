<div style="font-family: sans-serif; padding: 20px; background-color: #f9f9f9;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="font-weight: bold; color: #004d00;">TGK ට්‍රේඩර්ස්</h2>
        <h4 style="color: #004d00;">📦 වෙනස් කිරීම</h4>
        <span style="font-size: 14px; color: #555;">
            {{ \Carbon\Carbon::parse($settingDate)->format('Y-m-d') }}
        </span>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
        <thead style="background-color: #004d00; color: white;">
            <tr>
                <th style="padding: 8px; border: 1px solid #ccc;">විකුණුම්කරු</th>
                <th style="padding: 8px; border: 1px solid #ccc;">මලු</th>
                <th style="padding: 8px; border: 1px solid #ccc;">වර්ගය</th>
                <th style="padding: 8px; border: 1px solid #ccc;">බර</th>
                <th style="padding: 8px; border: 1px solid #ccc;">මිල</th>
                <th style="padding: 8px; border: 1px solid #ccc;">මුළු මුදල</th>
                <th style="padding: 8px; border: 1px solid #ccc;">බිල්පත් අංකය</th>
                <th style="padding: 8px; border: 1px solid #ccc;">පාරිභෝගික කේතය</th>
                <th style="padding: 8px; border: 1px solid #ccc;">වර්ගය (type)</th>
                <th style="padding: 8px; border: 1px solid #ccc;">දිනය සහ වේලාව</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($entries as $code => $group)
                @php
                    $original = $group->firstWhere('type', 'original');
                    $updated = $group->firstWhere('type', 'updated');
                    $deleted = $group->firstWhere('type', 'deleted');
                @endphp

                @if ($original)
                    <tr style="background-color: #d4edda; color: #155724;">
                        <td style="padding: 8px; border: 1px solid #ccc;">{{ $original->code }}</td>
                        <td style="padding: 8px; border: 1px solid #ccc;">{{ $original->packs }}</td>
                        <td style="padding: 8px; border: 1px solid #ccc;">{{ $original->item_name }}</td>
                        <td style="padding: 8px; border: 1px solid #ccc;">{{ $original->weight }}</td>
                        <td style="padding: 8px; border: 1px solid #ccc;">{{ number_format($original->price_per_kg, 2) }}</td>
                        <td style="padding: 8px; border: 1px solid #ccc;">{{ number_format($original->total, 2) }}</td>
                        <td style="padding: 8px; border: 1px solid #ccc;">{{ $original->bill_no }}</td>
                        <td style="padding: 8px; border: 1px solid #ccc;">{{ strtoupper($original->customer_code) }}</td>
                        <td style="padding: 8px; border: 1px solid #ccc;">{{ $original->type }}</td>
                        <td style="padding: 8px; border: 1px solid #ccc;">{{ $original->original_created_at->timezone('Asia/Colombo')->format('Y-m-d H:i') }}</td>
                    </tr>
                @endif
                
                @if ($updated)
                    <tr style="background-color: #fff3cd; color: #856404;">
                        <td style="padding: 8px; border: 1px solid #ccc;">{{ $updated->code }}</td>
                        <td style="padding: 8px; border: 1px solid #ccc; {{ $original && $updated->packs != $original->packs ? 'color: red; font-weight: bold;' : '' }}">{{ $updated->packs }}</td>
                        <td style="padding: 8px; border: 1px solid #ccc; {{ $original && $updated->item_name != $original->item_name ? 'color: red; font-weight: bold;' : '' }}">{{ $updated->item_name }}</td>
                        <td style="padding: 8px; border: 1px solid #ccc; {{ $original && $updated->weight != $original->weight ? 'color: red; font-weight: bold;' : '' }}">{{ $updated->weight }}</td>
                        <td style="padding: 8px; border: 1px solid #ccc; {{ $original && $updated->price_per_kg != $original->price_per_kg ? 'color: red; font-weight: bold;' : '' }}">{{ number_format($updated->price_per_kg, 2) }}</td>
                        <td style="padding: 8px; border: 1px solid #ccc; {{ $original && $updated->total != $original->total ? 'color: red; font-weight: bold;' : '' }}">{{ number_format($updated->total, 2) }}</td>
                        <td style="padding: 8px; border: 1px solid #ccc;">{{ $updated->bill_no }}</td>
                        <td style="padding: 8px; border: 1px solid #ccc; {{ $original && $updated->customer_code != $original->customer_code ? 'color: red; font-weight: bold;' : '' }}">{{ strtoupper($updated->customer_code) }}</td>
                        <td style="padding: 8px; border: 1px solid #ccc;">{{ $updated->type }}</td>
                        <td style="padding: 8px; border: 1px solid #ccc;">{{ $original->created_at->timezone('Asia/Colombo')->format('Y-m-d H:i') }}</td>
                    </tr>
                @endif

                @if ($deleted)
                    <tr style="background-color: #f8d7da; color: #721c24;">
                        <td style="padding: 8px; border: 1px solid #ccc;">{{ $deleted->code }}</td>
                        <td style="padding: 8px; border: 1px solid #ccc; {{ $original && $deleted->packs != $original->packs ? 'color: red; font-weight: bold;' : '' }}">{{ $deleted->packs }}</td>
                        <td style="padding: 8px; border: 1px solid #ccc; {{ $original && $deleted->item_name != $original->item_name ? 'color: red; font-weight: bold;' : '' }}">{{ $deleted->item_name }}</td>
                        <td style="padding: 8px; border: 1px solid #ccc; {{ $original && $deleted->weight != $original->weight ? 'color: red; font-weight: bold;' : '' }}">{{ $deleted->weight }}</td>
                        <td style="padding: 8px; border: 1px solid #ccc; {{ $original && $deleted->price_per_kg != $original->price_per_kg ? 'color: red; font-weight: bold;' : '' }}">{{ number_format($deleted->price_per_kg, 2) }}</td>
                        <td style="padding: 8px; border: 1px solid #ccc; {{ $original && $deleted->total != $original->total ? 'color: red; font-weight: bold;' : '' }}">{{ number_format($deleted->total, 2) }}</td>
                        <td style="padding: 8px; border: 1px solid #ccc;">{{ $deleted->bill_no }}</td>
                        <td style="padding: 8px; border: 1px solid #ccc; {{ $original && $deleted->customer_code != $original->customer_code ? 'color: red; font-weight: bold;' : '' }}">{{ strtoupper($deleted->customer_code) }}</td>
                        <td style="padding: 8px; border: 1px solid #ccc;">{{ $deleted->type }}</td>
                        <td style="padding: 8px; border: 1px solid #ccc;">{{ $deleted->created_at->timezone('Asia/Colombo')->format('Y-m-d H:i') }}</td>
                    </tr>
                @endif

            @empty
                <tr>
                    <td colspan="10" style="text-align: center; padding: 8px; border: 1px solid #ccc;">සටහන් කිසිවක් සොයාගෙන නොමැත</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>