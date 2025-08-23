<?php
// app/Http/Controllers/ReportController.php

namespace App\Http\Controllers;

use App\Models\IncomeExpenses;
use App\Models\SalesHistory;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\GrnEntry;// Replace with your actual model name
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\DynamicReportExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Salesadjustment;


class ReportController extends Controller
{
    public function index()
    {

        $suppliers = Sale::select('supplier_code')->distinct()->pluck('supplier_code');
        return view('dashboard.reports.salesbasedonsuppliers', compact('suppliers'));
    }

    public function fetch(Request $request)
    {
        // Log the incoming request data to check what values are being sent
        Log::info('Report Fetch Request:', $request->all());

        // Determine date range for filtering
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && $endDate) {
            // If a date range is provided, query the Salesadjustment table
            $query = SalesHistory::query();

            // Apply date range filter
            $query->whereBetween('Date', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);

            // Filter by supplier_code (optional) for Salesadjustment
            if ($request->filled('supplier_code') && $request->supplier_code !== 'all') {
                $query->where('supplier_code', $request->supplier_code);
            }

            // Filter by GRN code if selected (assuming 'code' applies to Salesadjustment as well)
            if ($request->filled('code')) {
                $query->where('code', $request->code);
            }

        } else {
            // If no date range, continue to query the Sale table
            $query = Sale::query();

            // Filter by supplier_code (optional) for Sale
            if ($request->filled('supplier_code') && $request->supplier_code !== 'all') {
                $query->where('supplier_code', $request->supplier_code);
            }

            // Filter by GRN code if selected for Sale
            if ($request->filled('code')) {
                $query->where('code', $request->code);
            }
        }

        // Log the final built query
        Log::info('Report Fetch Final Query:', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);

        // Select common fields that exist in both models, or adjust based on which model is queried
        $records = $query->get([
            'supplier_code',
            'code',
            'bill_no',
            'packs',
            'weight',
            'price_per_kg',
            'item_code',
            'total',
            'customer_code',
            'created_at'
        ]);

        return view('dashboard.reports.resultsalesbasedonsuppliers', [
            'records' => $records,
            'shop_no' => 'C11',
            'filters' => $request->all()
        ]);
    }

    public function fetchItemReport(Request $request)
    {
        $validated = $request->validate([
            'item_code' => 'required',
            'supplier_code' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Determine which model to query based on the presence of a date range
        if ($startDate && $endDate) {
            // If both start_date and end_date are provided, query Salesadjustment
            $query = SalesHistory::query();

            // Apply date range filter
            $query->whereBetween('Date', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);

        } else {
            // Otherwise, query Sale (default behavior)
            $query = Sale::query();
        }

        // Apply common filters to the selected query
        $query->where('item_code', $validated['item_code']);

        if (!empty($request->supplier_code) && $request->supplier_code !== 'all') {
            $query->where('supplier_code', $request->supplier_code);
        }

        $sales = $query->get([
            'item_code',
            'packs',
            'weight',
            'price_per_kg',
            'total',
            'customer_code',
            'supplier_code',
            'bill_no',
            'item_name',
            'created_at',
            'code',
            // Include created_at for consistency and potential display
        ]);

        return view('dashboard.reports.item-wise-report', [
            'sales' => $sales,
            'filters' => $request->all()
        ]);
    }

    public function getweight(Request $request)
    {
        $grnCode = $request->input('grn_code');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Choose base query depending on date range
        if ($startDate && $endDate) {
            $query = SalesHistory::selectRaw('item_name, item_code, SUM(packs) as packs, SUM(weight) as weight, SUM(total) as total')
                ->whereBetween('Date', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);
        } else {
            $query = Sale::selectRaw('item_name, item_code, SUM(packs) as packs, SUM(weight) as weight, SUM(total) as total');
        }

        // Filter by GRN code if given
        if (!empty($grnCode)) {
            $query->where('code', $grnCode);
        }

        // Group by item name & code
        $sales = $query->groupBy('item_name', 'item_code')
            ->orderBy('item_name', 'asc')
            ->get();

        // Only fetch GRN entry if code given
        $selectedGrnEntry = !empty($grnCode)
            ? GrnEntry::where('code', $grnCode)->first()
            : null;

        return view('dashboard.reports.weight-based-report', [
            'sales' => $sales,
            'selectedGrnCode' => $grnCode,
            'selectedGrnEntry' => $selectedGrnEntry,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filters' => $request->all(),
        ]);
    }

    public function getGrnSalecodereport(Request $request)
    {
        $grnCode = $request->input('grn_code');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if (!$grnCode) {
            return redirect()->back()->withErrors('Please select a GRN code.');
        }

        // Determine which model to query based on the presence of a date range
        if ($startDate && $endDate) {
            // If both start_date and end_date are provided, query Salesadjustment
            $query = SalesHistory::query();

            // Apply the date filter using Carbon for precision
            $query->whereBetween('Date', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);

        } else {
            // Otherwise, query Sale (default behavior)
            $query = Sale::query();
        }

        // Apply the GRN code filter to the selected query
        $query->where('code', $grnCode);

        $sales = $query->orderBy('created_at', 'asc')->get();
        $selectedGrnEntry = GrnEntry::where('code', $grnCode)->first();

        return view('dashboard.reports.grn_sale_code_report', [
            'sales' => $sales,
            'selectedGrnCode' => $grnCode,
            'selectedGrnEntry' => $selectedGrnEntry,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filters' => $request->all(),
        ]);
    }
    public function getSalesFilterReport(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Determine which model to query based on the presence of a date range
        if ($startDate && $endDate) {
            // If both start_date and end_date are provided, query Salesadjustment
            $query = SalesHistory::query();

            // Apply the date range filter using Carbon for robustness
            $query->whereBetween('Date', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);

        } else {
            // Otherwise, query Sale (default behavior)
            $query = Sale::query();
        }

        // Apply other filters if present, regardless of the chosen model
        if ($request->filled('supplier_code')) {
            $query->where('supplier_code', $request->input('supplier_code'));
        }

        if ($request->filled('customer_code')) {
            $query->where('customer_code', $request->input('customer_code'));
        }

        if ($request->filled('item_code')) {
            $query->where('item_code', $request->input('item_code'));
        }

        // Apply ordering
        switch ($request->input('order_by', 'id_desc')) { // Default to id_desc
            case 'id_asc':
                $query->orderBy('id', 'asc');
                break;
            case 'customer_code_asc':
                $query->orderBy('customer_code', 'asc');
                break;
            case 'customer_code_desc':
                $query->orderBy('customer_code', 'desc');
                break;
            case 'item_name_asc':
                $query->orderBy('item_name', 'asc');
                break;
            case 'item_name_desc':
                $query->orderBy('item_name', 'desc');
                break;
            case 'total_desc':
                $query->orderBy('total', 'desc');
                break;
            case 'total_asc':
                $query->orderBy('total', 'asc');
                break;
            case 'weight_desc':
                $query->orderBy('weight', 'desc');
                break;
            case 'weight_asc':
                $query->orderBy('weight', 'asc');
                break;
            case 'id_desc':
            default:
                $query->orderBy('id', 'desc');
                break;
        }

        $sales = $query->get([
            'code',
            'packs',
            'item_name',
            'weight',
            'price_per_kg',
            'total',
            'bill_no',
            'customer_code',
            'created_at' // Ensure created_at is selected for date filtering
        ]);

        // Calculate grand total
        $grandTotal = $sales->sum('total');

        // Pass data to the report view
        return view('dashboard.reports.sales_filter_report', compact('sales', 'grandTotal', 'request'));
    }
   public function getGrnSalesOverviewReport()
{
    // Fetch all GRN entries
    $grnEntries = GrnEntry::all();

    $reportData = [];

    foreach ($grnEntries->groupBy('code') as $code => $entries) {

        // --- GRN Totals ---
        $totalOriginalPacks = $entries->sum('original_packs');
        $totalOriginalWeight = $entries->sum('original_weight');

        $remainingPacks = $entries->sum('packs');
        $remainingWeight = $entries->sum('weight');

        // --- Sold quantities ---
        $totalSoldPacks = $totalOriginalPacks - $remainingPacks;
        $totalSoldWeight = $totalOriginalWeight - $remainingWeight;

        // --- Total sales value ---
        $currentSales = Sale::where('code', $code)->get();
        $historicalSales = SalesHistory::where('code', $code)->get();
        $relatedSales = $currentSales->merge($historicalSales);
        $totalSalesValueForGrn = $relatedSales->sum('total');

        $reportData[] = [
            'date' => Carbon::parse($entries->first()->created_at)
                ->timezone('Asia/Colombo')
                ->format('Y-m-d H:i:s'),
            'grn_code' => $code,
            'item_name' => $entries->first()->item_name,
            'original_packs' => $totalOriginalPacks,
            'original_weight' => $totalOriginalWeight,
            'sold_packs' => $totalSoldPacks,
            'sold_weight' => $totalSoldWeight, // keep numeric
            'total_sales_value' => $totalSalesValueForGrn,
            'remaining_packs' => $remainingPacks,
            'remaining_weight' => $remainingWeight, // keep numeric
        ];
    }

    return view('dashboard.reports.grn_sales_overview_report', [
        'reportData' => collect($reportData)
    ]);
}



   public function getGrnSalesOverviewReport2()
{
    // Fetch all GRN entries
    $grnEntries = GrnEntry::all();

    $reportData = [];

    // Group by item_name
    $grouped = $grnEntries->groupBy('item_name');

    foreach ($grouped as $itemName => $entries) {
        $originalPacks = 0;
        $originalWeight = 0;
        $soldPacks = 0;
        $soldWeight = 0;
        $totalSalesValue = 0;
        $remainingPacks = 0;
        $remainingWeight = 0;

        foreach ($entries as $grnEntry) {
            // Fetch current and historical sales for this GRN code
            $currentSales = Sale::where('code', $grnEntry->code)->get();
            $historicalSales = SalesHistory::where('code', $grnEntry->code)->get();
            $relatedSales = $currentSales->merge($historicalSales);

            // Total weight sold and total sales value for this GRN
            $totalSoldWeight = $relatedSales->sum('weight');
            $totalSalesValueForGrn = $relatedSales->sum('total');

            // Sum original packs and weight
            $originalPacks += $grnEntry->original_packs;
            $originalWeight += $grnEntry->original_weight;

            // Sum sold packs and weight
            $soldPacks += $grnEntry->original_packs - $grnEntry->packs;
            $soldWeight += $grnEntry->original_weight - $grnEntry->weight;

            // Sum remaining packs and weight (direct from GRN entry)
            $remainingPacks += $grnEntry->packs;
            $remainingWeight += $grnEntry->weight;

            // Sum total sales value
            $totalSalesValue += $totalSalesValueForGrn;
        }

        $reportData[] = [
            'item_name' => $itemName,
            'original_packs' => $originalPacks,
            'original_weight' => $originalWeight,
            'sold_packs' => $soldPacks,
            'sold_weight' => $soldWeight,
            'total_sales_value' => $totalSalesValue,
            'remaining_packs' => $remainingPacks,
            'remaining_weight' => $remainingWeight,
        ];
    }

    return view('dashboard.reports.grn_sales_overview_report2', [
        'reportData' => collect($reportData)
    ]);
}

    public function downloadReport(Request $request, $reportType, $format)
    {
        list($reportData, $headings, $reportTitle) = $this->getReportData($reportType, $request->all());

        if ($format === 'excel') {
            $filename = str_replace(' ', '-', $reportTitle) . '_' . Carbon::now()->format('Y-m-d') . '.xlsx';
            return Excel::download(new DynamicReportExport($reportData, $headings), $filename);
        }

        if ($format === 'pdf') {
            $filename = str_replace(' ', '-', $reportTitle) . '_' . Carbon::now()->format('Y-m-d') . '.pdf';

            $pdf = Pdf::loadView('reports.generic_report_pdf', compact('reportData', 'headings', 'reportTitle'))
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'defaultFont' => 'NotoSansSinhala',
                ]);

            return $pdf->download($filename);
        }

        abort(404, 'Invalid report format.');
    }


    /**
     * This function fetches and formats the data for a given report type.
     * You will need to customize this based on your report logic.
     */
    protected function getReportData($reportType, $filters = [])
    {
        $reportData = collect();
        $headings = [];
        $reportTitle = 'Report';

        switch ($reportType) {
            case 'supplier-sales':
                $reportTitle = 'Supplier Sales Report';

                $records = Sale::query()
                    ->when(isset($filters['code']), function ($query) use ($filters) {
                        return $query->where('code', $filters['code']);
                    })
                    ->when(isset($filters['date_from']), function ($query) use ($filters) {
                        return $query->whereDate('created_at', '>=', $filters['date_from']);
                    })
                    ->when(isset($filters['date_to']), function ($query) use ($filters) {
                        return $query->whereDate('created_at', '<=', $filters['date_to']);
                    })
                    ->get();

                $headings = ['Bill No', 'Packs', 'Weight (kg)', 'Price per kg', 'Total', 'Customer Code', 'Date', 'Shop No'];
                $reportData = $records->map(function ($row) {
                    return [
                        $row->bill_no,
                        $row->packs,
                        $row->weight,
                        $row->price_per_kg,
                        $row->total,
                        $row->customer_code,
                        Carbon::parse($row->created_at)->format('Y-m-d H:i'),
                        'N/A',
                    ];
                });
                break;

            case 'grn-sales-overview':
                $reportTitle = 'GRN Sales Overview Report';

                $records = GrnEntry::query()
                    ->when(isset($filters['grn_code']), function ($query) use ($filters) {
                        return $query->where('grn_code', $filters['grn_code']);
                    })
                    ->get();

                $headings = ['GRN Code', 'Item Code', 'Item Name', 'Original Packs', 'Current Packs', 'Weight (kg)'];
                $reportData = $records->map(function ($row) {
                    return [
                        $row->code,
                        $row->item_code,
                        $row->item_name,
                        $row->original_packs,
                        $row->packs,
                        $row->weight,
                    ];
                });
                break;

            case 'item-wise-report':
                $reportTitle = 'Item-wise Report';

                $query = Sale::query()
                    ->when(isset($filters['item_code']), function ($query) use ($filters) {
                        return $query->where('item_code', $filters['item_code']);
                    })
                    ->when(isset($filters['supplier_code']) && $filters['supplier_code'] !== 'all', function ($query) use ($filters) {
                        return $query->where('supplier_code', $filters['supplier_code']);
                    })
                    ->when(isset($filters['start_date']), function ($query) use ($filters) {
                        return $query->whereDate('created_at', '>=', $filters['start_date']);
                    })
                    ->when(isset($filters['end_date']), function ($query) use ($filters) {
                        return $query->whereDate('created_at', '<=', $filters['end_date']);
                    });

                $records = $query->get();

                $headings = ['Bill No', 'Item Code', 'Item Name', 'Packs', 'Weight (kg)', 'Price per kg', 'Total', 'Customer Code', 'Supplier Code'];
                $reportData = $records->map(function ($row) {
                    return [
                        $row->bill_no,
                        $row->item_code,
                        $row->item_name,
                        $row->packs,
                        $row->weight,
                        $row->price_per_kg,
                        $row->total,
                        $row->customer_code,
                        $row->supplier_code,
                    ];
                });
                break;

            case 'grn-sales-report':
                $reportTitle = 'GRN-based Sales Report';

                $query = Sale::query()
                    ->when(isset($filters['grn_code']), function ($query) use ($filters) {
                        return $query->where('code', $filters['grn_code']);
                    })
                    ->when(isset($filters['start_date']), function ($query) use ($filters) {
                        return $query->whereDate('created_at', '>=', $filters['start_date']);
                    })
                    ->when(isset($filters['end_date']), function ($query) use ($filters) {
                        return $query->whereDate('created_at', '<=', $filters['end_date']);
                    });

                $records = $query->orderBy('created_at', 'asc')->get();

                $headings = ['Item Code', 'Item Name', 'Packs', 'Weight (kg)', 'Total'];
                $reportData = $records->map(function ($row) {
                    return [
                        $row->item_code,
                        $row->item_name,
                        $row->packs,
                        $row->weight,
                        $row->total,
                    ];
                });
                break;

            case 'grn-sale-code-report':
                $reportTitle = 'GRN Code-based Sales Report';

                $query = Sale::query()
                    ->when(isset($filters['grn_code']), function ($query) use ($filters) {
                        return $query->where('code', $filters['grn_code']);
                    })
                    ->when(isset($filters['start_date']), function ($query) use ($filters) {
                        return $query->whereDate('created_at', '>=', $filters['start_date']);
                    })
                    ->when(isset($filters['end_date']), function ($query) use ($filters) {
                        return $query->whereDate('created_at', '<=', $filters['end_date']);
                    });

                $records = $query->orderBy('created_at', 'asc')->get();

                $headings = ['Item Code', 'Item Name', 'Packs', 'Weight (kg)', 'Total'];
                $reportData = $records->map(function ($row) {
                    return [
                        $row->item_code,
                        $row->item_name,
                        $row->packs,
                        $row->weight,
                        $row->total,
                    ];
                });
                break;
        }

        return [$reportData, $headings, $reportTitle];
    }
    public function salesAdjustmentReport(Request $request)
    {
        $code = $request->input('code');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Salesadjustment::query();

        if ($code) {
            $query->where('code', $code);
        }
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $entries = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('dashboard.reports.salesadjustment', compact('entries', 'code', 'startDate', 'endDate'));
    }
    public function financialReport()
    {
        $records = IncomeExpenses::select('customer_short_name', 'bill_no', 'description', 'amount', 'loan_type')
            ->whereDate('created_at', Carbon::today())
            ->get();

        $reportData = [];
        $totalDr = 0;
        $totalCr = 0;

        foreach ($records as $record) {
            $dr = null;
            $cr = null;

            // Build description
            $desc = $record->customer_short_name;
            if (!empty($record->bill_no)) {
                $desc .= " ({$record->bill_no})";
            }
            $desc .= " - {$record->description}";

            if (in_array($record->loan_type, ['old', 'ingoing'])) {
                $dr = $record->amount;
                $totalDr += $record->amount;
            } elseif (in_array($record->loan_type, ['today', 'outgoing'])) {
                $cr = $record->amount;
                $totalCr += $record->amount;
            }

            $reportData[] = [
                'description' => $desc,
                'dr' => $dr,
                'cr' => $cr
            ];
        }

        // Add Sales total
        $salesTotal = Sale::sum('total');
        $totalDr += $salesTotal;
        $reportData[] = [
            'description' => 'Sales Total',
            'dr' => $salesTotal,
            'cr' => null
        ];

        // Get Profit from SellingKGTotal
        $profitTotal = Sale::sum('SellingKGTotal');

        // 🆕 New: Calculate Total Damages
        $totalDamages = GrnEntry::select(DB::raw('SUM(wasted_weight * PerKGPrice)'))
            ->value(DB::raw('SUM(wasted_weight * PerKGPrice)'));

        // Handle case where result is null
        $totalDamages = $totalDamages ?? 0;

        return view('dashboard.reports.financial', compact(
            'reportData',
            'totalDr',
            'totalCr',
            'salesTotal',
            'profitTotal',
            'totalDamages' // 🆕 New: Pass the total damages to the view
        ));
    }
public function salesReport(Request $request)
{
    $query = Sale::query()->whereNotNull('bill_no')->where('bill_no', '<>', '');

    // Supplier filter
    if ($request->filled('supplier_code')) {
        $query->where('supplier_code', $request->supplier_code);
    }

    // Item filter
    if ($request->filled('item_code')) {
        $query->where('item_code', $request->item_code);
    }

    // Customer short name filter
    if ($request->filled('customer_short_name')) {
        $search = $request->customer_short_name;
        $query->where(function ($q) use ($search) {
            $q->where('customer_code', 'like', '%' . $search . '%')
              ->orWhereIn('customer_code', function ($sub) use ($search) {
                  $sub->select('short_name')
                      ->from('customers')
                      ->where('name', 'like', '%' . $search . '%');
              });
        });
    }

    // Customer code filter
    if ($request->filled('customer_code')) {
        $query->where('customer_code', $request->customer_code);
    }

    // Bill No filter
    if ($request->filled('bill_no')) {
        $query->where('bill_no', $request->bill_no);
    }

    $salesByBill = $query->get()->groupBy('bill_no');

    return view('dashboard.reports.new_sales_report', compact('salesByBill'));
}

public function grnReport(Request $request)
{
    $code = $request->input('code');

    $grnQuery = GrnEntry::query();
    if ($code) {
        $grnQuery->where('code', $code);
    }
    $grnEntries = $grnQuery->get();

    $groupedData = [];
    $reportData = [];

    foreach ($grnEntries as $entry) {
        // --- Sales for Cards ---
        $sales = Sale::where('code', $entry->code)->get([
            'code', 'customer_code', 'item_code', 'supplier_code', 'weight', 'price_per_kg', 'total', 'packs', 'item_name'
        ]);
        if ($sales->isEmpty()) {
            $sales = SalesHistory::where('code', $entry->code)->get([
                'code', 'customer_code', 'item_code', 'supplier_code', 'weight', 'price_per_kg', 'total', 'packs', 'item_name'
            ]);
        }

        $totalSales = $sales->sum('total'); // Sum of sales total
        $damageValue = $entry->wasted_weight * $entry->PerKGPrice; // Damage value

        $groupedData[$entry->code] = [
            'purchase_price' => $entry->total_grn,
            'item_name' => $entry->item_name, // <-- Add item_name here
            'sales' => $sales,
            'damage' => [
                'wasted_packs' => $entry->wasted_packs,
                'wasted_weight' => $entry->wasted_weight,
                'damage_value' => $damageValue
            ],
            'profit' => $entry->total_grn - $totalSales - $damageValue,
            'updated_at' => $entry->updated_at,
        ];

        // --- Summary Table Data ---
        $relatedSales = $sales; // For reportData, same as above
        $totalSoldPacks = $relatedSales->sum('packs');
        $totalSoldWeight = $relatedSales->sum('weight');
        $totalSalesValueForGrn = $relatedSales->sum('total');

        $totalOriginalPacks = $entry->original_packs;
        $totalOriginalWeight = $entry->original_weight;

        $remainingPacks = $totalOriginalPacks - $totalSoldPacks;
        $remainingWeight = $totalOriginalWeight - $totalSoldWeight;

        $reportData[] = [
            'grn_code' => $entry->code,
            'item_name' => $entry->item_name,
            'original_packs' => $totalOriginalPacks,
            'original_weight' => $totalOriginalWeight,
            'sold_packs' => $totalSoldPacks,
            'sold_weight' => $totalSoldWeight,
            'total_sales_value' => $totalSalesValueForGrn,
            'remaining_packs' => $remainingPacks,
            'remaining_weight' => $remainingWeight,
        ];
    }

    return view('dashboard.reports.grn', [
        'groupedData' => $groupedData,
        'reportData' => collect($reportData)
    ]);
}



}




