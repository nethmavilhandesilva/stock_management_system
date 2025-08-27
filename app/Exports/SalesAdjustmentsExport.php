<?php
// app/Exports/SalesAdjustmentsExport.php

namespace App\Exports;

use App\Models\Salesadjustment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesAdjustmentsExport implements FromCollection, WithHeadings
{
    protected $code;
    protected $startDate;
    protected $endDate;

    public function __construct($code, $startDate, $endDate)
    {
        $this->code = $code;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $query = Salesadjustment::query();

        if ($this->code) {
            $query->where('code', $this->code);
        }
        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        // Fetch all data (not paginated)
        $entries = $query->orderBy('created_at', 'desc')->get();

        // Process the data to group original, updated, and deleted rows
        $grouped = $entries->groupBy('code');

        $data = collect();
        foreach ($grouped as $group) {
            $original = $group->firstWhere('type', 'original');
            $updated = $group->firstWhere('type', 'updated');
            $deleted = $group->firstWhere('type', 'deleted');

            if ($original) {
                $data->push([
                    'විකුණුම්කරු' => $original->code,
                    'මලු' => $original->packs,
                    'වර්ගය' => $original->item_name,
                    'බර' => $original->weight,
                    'මිල' => $original->price_per_kg,
                    'මුළු මුදල' => $original->total,
                    'බිල්පත් අංකය' => $original->bill_no,
                    'පාරිභෝගික කේතය' => strtoupper($original->customer_code),
                    'වර්ගය (type)' => $original->type,
                    'දිනය සහ වේලාව' => $original->original_created_at->timezone('Asia/Colombo')->format('Y-m-d H:i'),
                ]);
            }
            if ($updated) {
                $data->push([
                    'විකුණුම්කරු' => $updated->code,
                    'මලු' => $updated->packs,
                    'වර්ගය' => $updated->item_name,
                    'බර' => $updated->weight,
                    'මිල' => $updated->price_per_kg,
                    'මුළු මුදල' => $updated->total,
                    'බිල්පත් අංකය' => $updated->bill_no,
                    'පාරිභෝගික කේතය' => strtoupper($updated->customer_code),
                    'වර්ගය (type)' => $updated->type,
                    'දිනය සහ වේලාව' => $updated->created_at->timezone('Asia/Colombo')->format('Y-m-d H:i'),
                ]);
            }
            if ($deleted) {
                $data->push([
                    'විකුණුම්කරු' => $deleted->code,
                    'මලු' => $deleted->packs,
                    'වර්ගය' => $deleted->item_name,
                    'බර' => $deleted->weight,
                    'මිල' => $deleted->price_per_kg,
                    'මුළු මුදල' => $deleted->total,
                    'බිල්පත් අංකය' => $deleted->bill_no,
                    'පාරිභෝගික කේතය' => strtoupper($deleted->customer_code),
                    'වර්ගය (type)' => $deleted->type,
                    'දිනය සහ වේලාව' => $deleted->created_at->timezone('Asia/Colombo')->format('Y-m-d H:i'),
                ]);
            }
        }
        return $data;
    }

    public function headings(): array
    {
        return [
            'විකුණුම්කරු',
            'මලු',
            'වර්ගය',
            'බර',
            'මිල',
            'මුළු මුදල',
            'බිල්පත් අංකය',
            'පාරිභෝගික කේතය',
            'වර්ගය (type)',
            'දිනය සහ වේලාව',
        ];
    }
}
