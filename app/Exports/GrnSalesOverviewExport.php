<?php

// app/Exports/GrnSalesOverviewExport.php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GrnSalesOverviewExport implements FromCollection, WithHeadings, WithMapping
{
    private $reportData;

    public function __construct(array $reportData)
    {
        $this->reportData = $reportData;
    }

    public function collection()
    {
        return new Collection($this->reportData);
    }

    public function headings(): array
    {
        return [
            'වර්ගය',
            'මිලදී ගත් බර',
            'මිලදී ගත් මලු',
            'විකිණුම් බර',
            'විකිණුම් මලු',
            'එකතුව',
            'ඉතිරි බර',
            'ඉතිරි මලු',
            'GRN කේතය'
        ];
    }

    public function map($data): array
    {
        return [
            $data['item_name'],
            $data['original_weight'],
            $data['original_packs'],
            $data['sold_weight'],
            $data['sold_packs'],
            $data['total_sales_value'],
            $data['remaining_weight'],
            $data['remaining_packs'],
            $data['grn_code'],
        ];
    }
}