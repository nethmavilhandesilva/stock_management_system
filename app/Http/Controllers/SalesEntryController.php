<?php

namespace App\Http\Controllers;

use App\Models\GrnEntry; // Make sure this is correctly pointing to your GrnEntry model
use App\Models\Supplier;
use App\Models\Sale;
use App\Models\Item;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\salesadjustment;
use App\Models\SalesHistory;
use Carbon\Carbon;
use App\Models\Setting; // Import Carbon
use App\Models\CustomersLoan;
use Illuminate\Support\Facades\Mail;
use App\Mail\DayStartReport;
use App\Mail\CombinedReportsMail;
use App\Models\IncomeExpenses; // Optional if you use FPDI later for templates
use Fpdf\Fpdf;
use App\Mail\CombinedReportsMail2;
use Illuminate\Support\Facades\Validator;



class SalesEntryController extends Controller
{
    /**
     * Displays the sales entry form.
     * Now fetches ALL sales records, as none are removed from display.
     * The 'Processed' column is an internal flag, not a display filter.
     */
   public function create()
{
    $suppliers = Supplier::all();
    $items = GrnEntry::select('item_name', 'item_code', 'code')
        ->where('is_hidden', 0) // Add the condition here
        ->distinct()
        ->get();
    $entries = GrnEntry::where('is_hidden', 0)->get();
    
    // Fetch all items. We only need the 'no' (item code) and 'pack_cost' columns.
    $itemsArray = Item::select('no', 'pack_due')->get();
    
    // Create a lookup array for item pack costs
    $itemPackCosts = [];
    foreach ($itemsArray as $item) {
        $itemPackCosts[$item->no] = $item->pack_due;
    }

    // Fetch ALL sales records to display
   $sales = Sale::where('Processed', 'N')->get()->groupBy('customer_code');

// Add pack_due to each sale
foreach ($sales as $customerSales) {
    foreach ($customerSales as $sale) {
        $sale->pack_due = $itemPackCosts[$sale->item_code] ?? 0;
    }
}

    
    // Add pack_cost to each sale
    foreach ($sales as $sale) {
        $sale->pack_due = $itemPackCosts[$sale->item_code] ?? 0;
    }
    
    $customers = Customer::all();
    $totalSum = $sales->sum('total'); // Sum will now be for all displayed sales
    
    $unprocessedSales = Sale::whereIn('Processed', ['Y', 'N']) // Include both processed and unprocessed
        ->get();
    
    // Add pack_cost to each unprocessed sale
    foreach ($unprocessedSales as $sale) {
        $sale->pack_due = $itemPackCosts[$sale->item_code] ?? 0;
    }
    
    $billDate = Setting::value('value');

    $salesPrinted = Sale::where('bill_printed', 'Y')
        ->orderBy('created_at', 'desc')
        ->orderBy('bill_no') // Or ->orderBy('created_at') for chronological order
        ->get()
        ->groupBy('customer_code');
    
    // Add pack_cost to each printed sale
    foreach ($salesPrinted as $customerSales) {
        foreach ($customerSales as $sale) {
            $sale->pack_due = $itemPackCosts[$sale->item_code] ?? 0;
        }
    }
    
    $totalUnprocessedSum = $unprocessedSales->sum('total');
    
    $salesNotPrinted = Sale::where('bill_printed', 'N')
        ->orderBy('customer_code')
        ->get()
        ->groupBy('customer_code');
    
    // Add pack_cost to each not printed sale
    foreach ($salesNotPrinted as $customerSales) {
        foreach ($customerSales as $sale) {
            $sale->pack_due = $itemPackCosts[$sale->item_code] ?? 0;
        }
    }

    // Calculate total for unprocessed sales
    $totalUnprintedSum = Sale::where('bill_printed', 'N')->sum('total');
    
    $lastDayStartedSetting = Setting::where('key', 'last_day_started_date')->first();
    $lastDayStartedDate = $lastDayStartedSetting ? Carbon::parse($lastDayStartedSetting->value) : null;

    $nextDay = $lastDayStartedDate ? $lastDayStartedDate->addDay() : Carbon::now();
    
    $codes = Sale::select('code')
        ->distinct()
        ->orderBy('code')
        ->get();
    
    // For $salesArray, add pack_cost to each sale
    $salesArray = Sale::all();
    foreach ($salesArray as $sale) {
        $sale->pack_due = $itemPackCosts[$sale->item_code] ?? 0;
    }

    return view('dashboard', compact(
        'suppliers', 
        'items', 
        'entries', 
        'sales', 
        'customers', 
        'totalSum', 
        'unprocessedSales', 
        'salesPrinted', 
        'totalUnprocessedSum', 
        'salesNotPrinted', 
        'totalUnprintedSum', 
        'nextDay', 
        'codes', 
        'billDate',
        'salesArray',
        'itemsArray'
    ));
}


public function store(Request $request)
{
    // 🔹 1. Validate input manually (no redirects)
    $validator = Validator::make($request->all(), [
        'supplier_code' => 'required',
        'customer_code' => 'required|string|max:255',
        'customer_name' => 'nullable',
        'code' => 'required',
        'item_code' => 'required',
        'item_name' => 'required',
        'weight' => 'required|numeric',
        'price_per_kg' => 'required|numeric',
        'total' => 'required|numeric',
        'packs' => 'required|integer|min:1',
        'grn_entry_code' => 'required|string|exists:grn_entries,code',
        'original_weight' => 'nullable',
        'original_packs' => 'nullable',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422);
    }

    $validated = $validator->validated();

    try {
        DB::beginTransaction();

        // 🔹 2. Find GRN entry
        $grnEntry = GrnEntry::where('code', $validated['grn_entry_code'])->first();
        if (!$grnEntry) {
            return response()->json([
                'success' => false,
                'message' => 'Selected GRN entry not found for update.',
            ], 422);
        }

        // 🔹 3. Calculate costs
        $perKgPrice  = $grnEntry->PerKGPrice;
        $perKgTotal  = $perKgPrice * $validated['weight'];

        // 🔹 4. Bill number
        $lastBillNoSale    = (int) Sale::max('bill_no');
        $lastBillNoHistory = (int) SalesHistory::max('bill_no');
        $lastBillNo        = max($lastBillNoSale, $lastBillNoHistory);
        $newBillNo         = $lastBillNo ? $lastBillNo + 1 : 1000;

        // 🔹 5. Date from settings
        $settingDate = Setting::value('value') ?? now()->toDateString();

        // 🔹 6. Create Sale
        $loggedInUserId = auth()->user()->user_id;
        $uniqueCode     = $validated['customer_code'] . '-' . $loggedInUserId;
        $sellingKGTotal = $validated['total'] - $perKgTotal;
        $saleCode       = $grnEntry->code;

        $sale = Sale::create([
            'supplier_code'       => $validated['supplier_code'],
            'customer_code'       => strtoupper($validated['customer_code']),
            'customer_name'       => $validated['customer_name'],
            'code'                => $saleCode,
            'item_code'           => $validated['item_code'],
            'item_name'           => $validated['item_name'],
            'weight'              => $validated['weight'],
            'price_per_kg'        => $validated['price_per_kg'],
            'total'               => $validated['total'],
            'packs'               => $validated['packs'],
            'original_weight'     => $validated['original_weight'],
            'original_packs'      => $validated['original_packs'],
            'Processed'           => 'N',
            'FirstTimeBillPrintedOn' => null,
            'BillChangedOn'       => null,
            'CustomerBillEnteredOn' => now(),
            'UniqueCode'          => $uniqueCode,
            'PerKGPrice'          => $perKgPrice,
            'PerKGTotal'          => $perKgTotal,
            'SellingKGTotal'      => $sellingKGTotal,
            'Date'                => $settingDate,
            'bill_no'             => $newBillNo,
        ]);

        // 🔹 7. Update GRN stock
        $this->updateGrnRemainingStock($validated['grn_entry_code']);

        DB::commit();

        // 🔹 8. Return JSON response
        return response()->json([
            'success' => true,
            'data' => [
                'id'       => $sale->id,
                'Date'       => $sale->Date,
                'customer_code' => $sale->customer_code,
                'customer_name' => $sale->customer_name,
                'item_name'     => $sale->item_name,
                'item_code'     => $sale->item_code,
                'weight'        => $sale->weight,
                'price_per_kg'  => $sale->price_per_kg,
                'total'         => $sale->total,
                'packs'         => $sale->packs,
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to add sales entry and update GRN: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Failed to add sales entry',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

    public function markAllAsProcessed(Request $request)
    {
        try {
            DB::beginTransaction();

            Sale::where('Processed', 'N')->update([
                'Processed' => 'Y',
                'bill_printed' => DB::raw("IFNULL(bill_printed, 'N')") // Set to 'N' only if currently NULL
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'All sales with Processed = N are now marked as processed, and NULL bill_printed values set to N.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error marking all sales as processed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark sales as processed: ' . $e->getMessage()
            ], 500);
        }
    }
    public function markAsPrinted(Request $request)
    {
        \Log::info('markAsPrinted Request Data:', $request->all());

        $salesIds = $request->input('sales_ids');

        if (empty($salesIds)) {
            return response()->json(['status' => 'error', 'message' => 'No sales IDs provided.'], 400);
        }

        try {
            // Step 1: Check for an existing bill number among the provided sales IDs.
            // This is the key step. We query the database directly to find if any
            // of the records have already been processed and assigned a bill number.
            $existingBillNo = Sale::whereIn('id', $salesIds)
                ->where('processed', 'Y')
                ->whereNotNull('bill_no')
                ->first()?->bill_no;

            // Step 2: Determine the bill number to use.
            // If an existing bill number was found, use it. Otherwise, generate a new one.
            $billNoToUse = $existingBillNo;
            if (empty($billNoToUse)) {
                $billNoToUse = $this->generateNewBillNumber();
            }

            // Step 3: Update all sales records with the determined bill number.
            // We do this in a single transaction for reliability.
            \DB::transaction(function () use ($salesIds, $billNoToUse) {
                $salesRecords = Sale::whereIn('id', $salesIds)->get();

                foreach ($salesRecords as $sale) {
                    // If it's a reprint, update the timestamp for reprint history.
                    if ($sale->bill_printed === 'Y') {
                        $sale->BillReprintAfterChanges = now();
                    }

                    // Update the main fields for all selected records.
                    $sale->bill_printed = 'Y';
                    $sale->processed = 'Y';
                    $sale->bill_no = $billNoToUse;

                    // Set the first print date only if it hasn't been set before.
                    $sale->FirstTimeBillPrintedOn = $sale->FirstTimeBillPrintedOn ?? now();

                    $sale->save();
                }
            });

            \Log::info('Sales records updated successfully for IDs:', ['sales_ids' => $salesIds, 'bill_no' => $billNoToUse]);

            return response()->json([
                'status' => 'success',
                'message' => 'Sales marked as printed and reprint timestamp updated if needed!',
                'bill_no' => $billNoToUse
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating sales records:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'sales_ids' => $salesIds
            ]);
            return response()->json(['status' => 'error', 'message' => 'Failed to update sales records.'], 500);
        }
    }

    // Helper method to generate a new bill number
    private function generateNewBillNumber()
    {
        return \DB::transaction(function () {
            $bill = \App\Models\BillNumber::lockForUpdate()->first();
            if (!$bill) {
                $bill = \App\Models\BillNumber::create(['last_bill_no' => 999]);
            }
            $bill->last_bill_no += 1;
            $bill->save();
            return $bill->last_bill_no;
        });
    }

    public function update(Request $request, Sale $sale)
    {
        $validatedData = $request->validate([
            'customer_code' => 'required|string|max:255',
            'customer_name' => 'nullable|string|max:255',
            'code' => 'required|string|max:255',
            'supplier_code' => 'required|string|max:255',
            'item_code' => 'required|string|max:255',
            'item_name' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0',
            'price_per_kg' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'packs' => 'required|integer|min:0',
        ]);

        try {
            // Get the setting date value
            $settingDate = Setting::value('value');
            $formattedDate = Carbon::parse($settingDate)->format('Y-m-d');

            $oldPacks = $sale->packs;
            $oldWeight = $sale->weight;

            // --- Adjustment tracking for bill_printed ---
            if ($sale->bill_printed === 'Y') {
                $originalData = $sale->toArray();
                Salesadjustment::create([
                    'customer_code' => $originalData['customer_code'],
                    'supplier_code' => $originalData['supplier_code'],
                    'code' => $originalData['code'],
                    'item_code' => $originalData['item_code'],
                    'item_name' => $originalData['item_name'],
                    'weight' => $originalData['weight'],
                    'price_per_kg' => $originalData['price_per_kg'],
                    'total' => $originalData['total'],
                    'packs' => $originalData['packs'],
                    'bill_no' => $originalData['bill_no'],
                    'user_id' => 'c11',
                    'type' => 'original',
                    'original_created_at' => $sale->Date,
                    'original_updated_at' => $sale->updated_at,
                    'Date' => $formattedDate, // ✅ Add Date
                ]);
            }

            // Update the sale
            $sale->update([
                'customer_code' => $validatedData['customer_code'],
                'customer_name' => $validatedData['customer_name'] ?? $sale->customer_name,
                'code' => $validatedData['code'],
                'supplier_code' => $validatedData['supplier_code'],
                'item_code' => $validatedData['item_code'],
                'item_name' => $validatedData['item_name'],
                'weight' => $validatedData['weight'],
                'packs' => $validatedData['packs'],
                'price_per_kg' => $validatedData['price_per_kg'],
                'total' => $validatedData['total'],
                'updated' => 'Y',
                'BillChangedOn' => now(),
            ]);

            $this->updateGrnRemainingStock($validatedData['code']);

            // Save updated version as adjustment if needed
            if ($sale->bill_printed === 'Y') {
                $newData = $sale->fresh();
                Salesadjustment::create([
                    'customer_code' => $newData->customer_code,
                    'supplier_code' => $newData->supplier_code,
                    'code' => $newData->code,
                    'item_code' => $newData->item_code,
                    'item_name' => $newData->item_name,
                    'weight' => $newData->weight,
                    'price_per_kg' => $newData->price_per_kg,
                    'total' => $newData->total,
                    'packs' => $newData->packs,
                    'bill_no' => $newData->bill_no,
                    'user_id' => 'c11',
                    'type' => 'updated',
                    'original_created_at' => $newData->created_at,
                    'original_updated_at' => $newData->updated_at,
                    'Date' => $formattedDate, // ✅ Add Date
                ]);
            }

            return response()->json([
                'success' => true,
                'sale' => $sale->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update sales record: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function destroy(Sale $sale)
    {
        try {
            // Get the setting date value
            $settingDate = Setting::value('value');
            $formattedDate = Carbon::parse($settingDate)->format('Y-m-d');

            if ($sale->bill_printed === 'Y') {
                // Always create an "original" record
                Salesadjustment::create([
                    'customer_code' => $sale->customer_code,
                    'supplier_code' => $sale->supplier_code,
                    'code' => $sale->code,
                    'item_code' => $sale->item_code,
                    'item_name' => $sale->item_name,
                    'weight' => $sale->weight,
                    'price_per_kg' => $sale->price_per_kg,
                    'total' => $sale->total,
                    'packs' => $sale->packs,
                    'bill_no' => $sale->bill_no,
                    'type' => 'original',
                    'original_created_at' => $sale->Date,
                    'Date' => $formattedDate, // ✅ store setting date
                ]);

                // Always create a "deleted" record
                Salesadjustment::create([
                    'customer_code' => $sale->customer_code,
                    'supplier_code' => $sale->supplier_code,
                    'code' => $sale->code,
                    'item_code' => $sale->item_code,
                    'item_name' => $sale->item_name,
                    'weight' => $sale->weight,
                    'price_per_kg' => $sale->price_per_kg,
                    'total' => $sale->total,
                    'packs' => $sale->packs,
                    'bill_no' => $sale->bill_no,
                    'type' => 'deleted',
                    'original_created_at' => $sale->created_at,
                    'Date' => $formattedDate, // ✅ store setting date
                ]);
            }

            // Delete and update GRN stock
            $saleCode = $sale->code;
            $sale->delete();
            $this->updateGrnRemainingStock($saleCode);

            return response()->json([
                'success' => true,
                'message' => 'Sales record deleted successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting sale: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the sale.'
            ], 500);
        }
    }

    public function updateGrnRemainingStock(): void
    {
        // Fetch all GRN entries and group them by their unique 'code'
        $grnEntriesByCode = GrnEntry::all()->groupBy('code');

        // Fetch all sales and sales history entries
        $currentSales = Sale::all()->groupBy('code');
        $historicalSales = SalesHistory::all()->groupBy('code');

        foreach ($grnEntriesByCode as $grnCode => $entries) {
            // Calculate the total original packs and weight for the current GRN code
            $totalOriginalPacks = $entries->sum('original_packs');
            $totalOriginalWeight = $entries->sum('original_weight');
            $totalWastedPacks = $entries->sum('wasted_packs');
            $totalWastedWeight = $entries->sum('wasted_weight');

            // Sum up packs and weight from sales for this specific GRN code
            $totalSoldPacks = 0;
            if (isset($currentSales[$grnCode])) {
                $totalSoldPacks += $currentSales[$grnCode]->sum('packs');
            }
            if (isset($historicalSales[$grnCode])) {
                $totalSoldPacks += $historicalSales[$grnCode]->sum('packs');
            }

            $totalSoldWeight = 0;
            if (isset($currentSales[$grnCode])) {
                $totalSoldWeight += $currentSales[$grnCode]->sum('weight');
            }
            if (isset($historicalSales[$grnCode])) {
                $totalSoldWeight += $historicalSales[$grnCode]->sum('weight');
            }

            // Calculate remaining stock based on all original, sold, and wasted amounts
            $remainingPacks = $totalOriginalPacks - $totalSoldPacks - $totalWastedPacks;
            $remainingWeight = $totalOriginalWeight - $totalSoldWeight - $totalWastedWeight;

            // Update each individual GRN entry with the new remaining values
            foreach ($entries as $grnEntry) {
                $grnEntry->packs = max($remainingPacks, 0);
                $grnEntry->weight = max($remainingWeight, 0);
                $grnEntry->save();
            }
        }
    }

    public function saveAsUnprinted(Request $request)
    {
        // Validate the incoming request to ensure it's an array of IDs
        $validated = $request->validate([
            'sale_ids' => 'required|array',
            'sale_ids.*' => 'integer|exists:sales,id', // Check that each ID exists
        ]);

        if (!empty($validated['sale_ids'])) {
            // Update the records that match the IDs from the table
            // We set `is_printed` to `0` to mark them as unprinted
            // You might need to adjust the column name based on your database schema
            Sale::whereIn('id', $validated['sale_ids'])->update(['is_printed' => 0]);
        }

        return response()->json(['success' => true]);
    }

    public function clearAll(Request $request)
    {
        Sale::truncate();    // deletes all records from sales table
        GrnEntry::truncate();
        CustomersLoan::truncate();
        Salesadjustment::truncate(); // deletes all records from grn_entries table

        return back()->with('success', 'All data cleared from Sales and GRN Entries.');
    }
    public function getUnprintedSales($customer_code)
    {
        // Find all sales records for the given customer_code
        // where the bill_printed column has the value 'N'
        $sales = Sale::where('customer_code', $customer_code)
            ->where('bill_printed', 'N')
            ->get();

        // Return the sales records as a JSON response
        return response()->json($sales);
    }
    public function getAllSalesData()
    {
        try {
            // Fetch all sales records from the database
            $allSales = Sale::all();

            // Return the sales records as a JSON response
            return response()->json($allSales);

        } catch (\Exception $e) {
            // Log the full error for server-side debugging
            Log::error('Failed to retrieve sales data: ' . $e->getMessage(), [
                'exception_file' => $e->getFile(),
                'exception_line' => $e->getLine(),
                'exception_trace' => $e->getTraceAsString(),
            ]);

            // Return a detailed error response to the client
            return response()->json([
                'error' => 'Failed to retrieve sales data.',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }
    public function getAllSales()
    {
        $sales = Sale::all(); // or your logic
        return response()->json(['sales' => $sales]);
    }

    public function sendDailyCombinedReport()
    {
        try {
            // Get the current day's start and end dates
            $today = Carbon::today();
            $dayStartDate = $today->copy()->startOfDay();
            $dayEndDate = $today->copy()->endOfDay();

            // Collect the Day Start Report Data
            $dayStartReportData = DayStart::whereBetween('created_at', [$dayStartDate, $dayEndDate])->get();

            // Collect the GRN Report Data
            $grnReportData = Grn::whereBetween('created_at', [$dayStartDate, $dayEndDate])->get();

            // Collect the Sales Report Data
            $salesReportData = SalesHistory::whereBetween('created_at', [$dayStartDate, $dayEndDate])->get();

            // Send the email with all the collected data
            // This is line 507, where the fix is applied.
            Mail::send(new CombinedReportsMail($dayStartReportData, $grnReportData, $salesReportData, $today));

            return back()->with('success', 'Combined report email sent successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to send combined report email: ' . $e->getMessage());
            return back()->with('error', 'Failed to send combined report email.');
        }
    }

  public function dayStart(Request $request)
{
    try {
        DB::beginTransaction();

        // Validate the date input from modal
        $request->validate([
            'new_day_date' => 'required|date',
        ]);

        // Use the selected date from the modal
        $dayStartDate = Carbon::parse($request->new_day_date)->startOfDay();

        // --- Generate Day Start Report Data using GRN grouping logic ---
        $grnEntries = GrnEntry::all();
        $dayStartReportData = [];

        foreach ($grnEntries->groupBy('code') as $code => $entries) {
            $totalOriginalPacks = $entries->sum('original_packs');
            $totalOriginalWeight = $entries->sum('original_weight');

            $remainingPacks = $entries->sum('packs');
            $remainingWeight = $entries->sum('weight');

            $totalSoldPacks = $totalOriginalPacks - $remainingPacks;
            $totalSoldWeight = $totalOriginalWeight - $remainingWeight;

            // --- Total sales value (merge current + historical sales) ---
            $currentSales = Sale::where('code', $code)->get();
            $historicalSales = SalesHistory::where('code', $code)->get();
            $relatedSales = $currentSales->merge($historicalSales);
            $totalSalesValue = $relatedSales->sum('total');

            $totalWastedPacks = $entries->sum('wasted_packs');
            $totalWastedWeight = $entries->sum('wasted_weight');

            $dayStartReportData[] = [
                'date' => Carbon::parse($entries->first()->created_at)
                    ->timezone('Asia/Colombo')
                    ->format('Y-m-d H:i:s'),
                'grn_code' => $code,
                'item_name' => $entries->first()->item_name,
                'original_packs' => $totalOriginalPacks,
                'original_weight' => $totalOriginalWeight,
                'sold_packs' => $totalSoldPacks,
                'sold_weight' => $totalSoldWeight,
                'total_sales_value' => $totalSalesValue,
                'remaining_packs' => $remainingPacks,
                'remaining_weight' => $remainingWeight,
                'totalWastedPacks' => $totalWastedPacks,
                'totalWastedWeight' => $totalWastedWeight,
            ];
        }

        // --- Generate GRN Report Data ---
        $reportData = [];
        $grouped = $grnEntries->groupBy('item_name');
        $grnReportData = [];

        foreach ($grouped as $itemName => $entries) {
            $originalPacks = 0;
            $originalWeight = 0;
            $soldPacks = 0;
            $soldWeight = 0;
            $totalSalesValue = 0;
            $remainingPacks = 0;
            $remainingWeight = 0;

            foreach ($entries as $grnEntry) {
                $currentSales = Sale::where('code', $grnEntry->code)->get();
                $historicalSales = SalesHistory::where('code', $grnEntry->code)->get();
                $relatedSales = $currentSales->merge($historicalSales);

                $totalSalesValueForGrn = $relatedSales->sum('total');

                $originalPacks += $grnEntry->original_packs;
                $originalWeight += $grnEntry->original_weight;

                $soldPacks += $grnEntry->original_packs - $grnEntry->packs;
                $soldWeight += $grnEntry->original_weight - $grnEntry->weight;

                $remainingPacks += $grnEntry->packs;
                $remainingWeight += $grnEntry->weight;

                $totalSalesValue += $totalSalesValueForGrn;
            }

            $grnReportData[] = [
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

        // --- Weight-Based Report Data ---
        $weightBasedReportData = Sale::selectRaw('item_name, item_code, SUM(packs) as packs, SUM(weight) as weight, SUM(total) as total')
            ->groupBy('item_name', 'item_code')
            ->orderBy('item_name', 'asc')
            ->get();

        $salesByBill = Sale::query()
            ->whereNotNull('bill_no')
            ->where('bill_no', '<>', '')
            ->get()
            ->groupBy('bill_no');

        // Get the setting date value
        $settingDate = Setting::value('value');

        // --- Sales Adjustments ---
        $salesadjustments = Salesadjustment::whereDate('Date', $settingDate)
            ->orderBy('created_at', 'desc')
            ->get();

        // --- Financial Report Data ---
        $financialRecords = IncomeExpenses::select('customer_short_name', 'bill_no', 'description', 'amount', 'loan_type')
            ->whereDate('Date', $settingDate)
            ->get() ?? collect([]);

        $financialReportData = [];
        $totalDr = 0;
        $totalCr = 0;

        foreach ($financialRecords as $record) {
            $dr = null;
            $cr = null;

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

            $financialReportData[] = [
                'description' => $desc,
                'dr' => $dr,
                'cr' => $cr
            ];
        }

        $salesTotal = Sale::sum('total');
        $totalDr += $salesTotal;
        $financialReportData[] = [
            'description' => 'Sales Total',
            'dr' => $salesTotal,
            'cr' => null
        ];

        $profitTotal = Sale::sum('SellingKGTotal');
        $totalDamages = GrnEntry::select(DB::raw('SUM(wasted_weight * PerKGPrice)'))
            ->value(DB::raw('SUM(wasted_weight * PerKGPrice)')) ?? 0;

        // --- Loans ---
        $allLoans = CustomersLoan::all() ?? collect([]);
        $groupedLoans = $allLoans->groupBy('customer_short_name');
        $finalLoans = collect([]);

        foreach ($groupedLoans as $customerShortName => $loans) {
            $lastOldLoan = $loans->where('loan_type', 'old')
                ->sortByDesc(fn($l) => Carbon::parse($l->created_at))
                ->first();

            $firstTodayAfterOld = $loans->filter(function ($l) use ($lastOldLoan) {
                return $l->loan_type === 'today' &&
                       Carbon::parse($l->created_at) > (
                           $lastOldLoan ? Carbon::parse($lastOldLoan->created_at)
                                        : Carbon::parse('1970-01-01')
                       );
            })->sortBy(fn($l) => Carbon::parse($l->created_at))
              ->first();

            $highlightColor = null;

            if ($lastOldLoan && $firstTodayAfterOld) {
                $daysBetweenLoans = Carbon::parse($lastOldLoan->created_at)
                    ->diffInDays(Carbon::parse($firstTodayAfterOld->created_at));

                if ($daysBetweenLoans > 30) {
                    $highlightColor = 'red-highlight';
                } elseif ($daysBetweenLoans >= 14) {
                    $highlightColor = 'blue-highlight';
                }

                $extraTodayLoanExists = $loans->filter(function ($l) use ($firstTodayAfterOld) {
                    return $l->loan_type === 'today' &&
                           Carbon::parse($l->created_at) > Carbon::parse($firstTodayAfterOld->created_at);
                })->count() > 0;

                if ($extraTodayLoanExists) {
                    $highlightColor = null;
                }
            } elseif ($lastOldLoan && !$firstTodayAfterOld) {
                $daysSinceLastOldLoan = Carbon::parse($lastOldLoan->created_at)->diffInDays(Carbon::now());

                if ($daysSinceLastOldLoan > 30) {
                    $highlightColor = 'red-highlight';
                } elseif ($daysSinceLastOldLoan >= 14) {
                    $highlightColor = 'blue-highlight';
                }
            }

            $totalToday = $loans->where('loan_type', 'today')->sum('amount');
            $totalOld = $loans->where('loan_type', 'old')->sum('amount');
            $totalAmount = $totalToday - $totalOld;

            $finalLoans->push((object) [
                'customer_short_name' => $customerShortName,
                'total_amount' => $totalAmount,
                'highlight_color' => $highlightColor,
            ]);
        }

        // --- Send Combined Emails ---
        Mail::send(new CombinedReportsMail(
            $dayStartReportData,
            $grnReportData,
            $grnEntries,
            $dayStartDate,
            $weightBasedReportData,
            salesByBill: $salesByBill,
            salesadjustments: $salesadjustments,
            financialReportData: $financialReportData,
            financialTotalDr: $totalDr,
            financialTotalCr: $totalCr,
            financialProfit: $profitTotal,
            financialDamages: $totalDamages,
            profitTotal: $profitTotal,
            totalDamages: $totalDamages,
            loans: $allLoans,
            finalLoans: $finalLoans,
        ));

        Mail::send(new CombinedReportsMail2(
            $dayStartReportData,
            $grnReportData,
            $grnEntries,
            $dayStartDate,
            $weightBasedReportData,
            salesByBill: $salesByBill,
            salesadjustments: $salesadjustments,
            financialReportData: $financialReportData,
            financialTotalDr: $totalDr,
            financialTotalCr: $totalCr,
            financialProfit: $profitTotal,
            financialDamages: $totalDamages,
            profitTotal: $profitTotal,
            totalDamages: $totalDamages,
            loans: $allLoans,
            finalLoans: $finalLoans,
        ));

        // --- Archive Sales and Clear Table ---
        if ($grnEntries->isNotEmpty()) {
            $sales = Sale::all();
            $salesHistoryData = $sales->map(function ($sale) {
                return [
                    'Date' => $sale->Date,
                    'bill_no' => $sale->bill_no,
                    'code' => $sale->code,
                    'item_code' => $sale->item_code,
                    'item_name' => $sale->item_name,
                    'packs' => $sale->packs,
                    'weight' => $sale->weight,
                    'price_per_kg' => $sale->price_per_kg,
                    'total' => $sale->total,
                    'customer_code' => $sale->customer_code,
                    'customer_name' => $sale->customer_name,
                    'supplier_code' => $sale->supplier_code,
                    'bill_printed' => $sale->bill_printed,
                    'is_printed' => $sale->is_printed,
                    'PerKGPrice' => $sale->PerKGPrice,
                    'PerKGTotal' => $sale->PerKGTotal,
                    'SellingKGTotal' => $sale->SellingKGTotal,
                    'created_at' => $sale->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $sale->updated_at->format('Y-m-d H:i:s'),
                ];
            })->toArray();

            if (!empty($salesHistoryData)) {
                SalesHistory::insert($salesHistoryData);
            }
            Sale::truncate();
        }

        // --- Update Day Start Date in Settings ---
        Setting::updateOrCreate(
    ['key' => 'last_day_started_date'], // matching condition
    [
        'value' => $dayStartDate->format('Y-m-d'),
       
    ]
);

        DB::commit();

        return redirect()->back()->with('success', 'Day started for ' . $dayStartDate->format('Y-m-d') . '. Reports sent successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Day Start Failed: ' . $e->getMessage());
        return redirect()->back();
    }
}

    public function getLoanAmount(Request $request)
    {
        // Validate the request to ensure a customer_short_name is present.
        $request->validate(['customer_short_name' => 'required|string']);

        $customerShortName = $request->input('customer_short_name');

        // Sum of 'old' loan_type amounts
        $oldSum = CustomersLoan::where('customer_short_name', $customerShortName)
            ->where('loan_type', 'old')
            ->sum('amount');

        // Sum of 'today' loan_type amounts
        $todaySum = CustomersLoan::where('customer_short_name', $customerShortName)
            ->where('loan_type', 'today')
            ->sum('amount');

        // Calculate total loan amount based on your logic
        if ($todaySum == 0) {
            $totalLoanAmount = $oldSum;
        } else {
            $totalLoanAmount = $todaySum - $oldSum;
        }

        // Return the sum as a JSON response.
        return response()->json(['total_loan_amount' => $totalLoanAmount]);
    }
    public function listCodes()
    {
        // Get unique codes from sales
        $codes = Sale::select('code')->distinct()->orderBy('code')->get();
        return view('dashboard', compact('codes'));
    }

    public function showByCode($code)
    {
        // Get all sales with that code
        $sales = Sale::where('code', $code)->get();

        // Ensure at least one record exists
        $firstRecord = $sales->first();
        $itemCode = $firstRecord ? $firstRecord->item_code : null;
        $supplierCode = $firstRecord ? $firstRecord->supplier_code : null;

        // Defaults
        $packs_ratio_display = '0 / 0';
        $weight_ratio_display = '0.00 / 0.00';

        if ($firstRecord) {
            // Sum GRN values based only on rn.code
            $current_grn_packs = GrnEntry::where('code', $code)->sum('packs');
            $original_packs_grn = GrnEntry::where('code', $code)->sum('original_packs');
            $current_grn_weight = GrnEntry::where('code', $code)->sum('weight');
            $original_weight_grn = GrnEntry::where('code', $code)->sum('original_weight');

            $packs_ratio_display = $current_grn_packs . ' / ' . $original_packs_grn;
            $weight_ratio_display = number_format($current_grn_weight, 2) . ' / ' . number_format($original_weight_grn, 2);
        }

        return view('dashboard.sales.by_code', compact(
            'sales',
            'code',
            'supplierCode',
            'itemCode',
            'packs_ratio_display',
            'weight_ratio_display'
        ));
    }
    private function generateWeightBasedReport($grnCode = null, $startDate = null, $endDate = null)
    {
        if ($startDate && $endDate) {
            $query = SalesHistory::selectRaw('item_name, item_code, SUM(packs) as packs, SUM(weight) as weight, SUM(total) as total')
                ->whereBetween('Date', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);
        } else {
            $query = Sale::selectRaw('item_name, item_code, SUM(packs) as packs, SUM(weight) as weight, SUM(total) as total');
        }

        if (!empty($grnCode)) {
            $query->where('code', $grnCode);
        }

        return $query->groupBy('item_name', 'item_code')
            ->orderBy('item_name', 'asc')
            ->get();
    }

    public function saveReceiptFile(Request $request)
    {
        $html = $request->receipt_html;
        $customerName = $request->customer_name ?? 'customer';
        $billNo = $request->bill_no ?? 'N/A';

        // Folder path
        $folder = 'D:\\Receipts';

        // Create folder if it doesn't exist
        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        // Save HTML version as backup
        $htmlFilePath = $folder . '\\' . "Receipt_{$billNo}_{$customerName}.html";
        file_put_contents($htmlFilePath, $html);

        // Save PDF version with exact size of bill
        $pdfFilePath = $folder . '\\' . "Receipt_{$billNo}_{$customerName}.pdf";

        $pdf = new \FPDF('P', 'mm', [80, 200]);
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 10);
        $pdf->WriteHTML = function ($pdf, $htmlContent) {
            // Simple HTML parser for FPDF (or use HTML2FPDF library)
            // For now, just plain text conversion:
            $text = strip_tags($htmlContent);
            $pdf->MultiCell(0, 4, $text);
        };
        // Write HTML
        $pdf->WriteHTML($pdf, $html);

        // Output PDF to file
        $pdf->Output('F', $pdfFilePath);

        return response()->json([
            'success' => true,
            'message' => "Receipt saved successfully! HTML: {$htmlFilePath}, PDF: {$pdfFilePath}"
        ]);
    }
    // SaleController.php
    public function getNextBillNo()
    {
        // Get max bill_no from both tables
        $maxSale = Sale::max('bill_no') ?? 0;
        $maxHistory = SalesHistory::max('bill_no') ?? 0;
        $nextBillNo = max($maxSale, $maxHistory) + 1;

        // If no records exist, start from 1000
        if ($nextBillNo < 1000) {
            $nextBillNo = 1000;
        }

        return response()->json(['nextBillNo' => $nextBillNo]);
    }
   public function updateBalance(Request $request)
{
    $request->validate([
        'balance' => 'required|numeric|min:0',
    ]);

    $setting = Setting::first();

    if (!$setting) {
        $setting = new Setting();
    }

    $setting->balance = $request->balance;

    // store the "value" column as Date_of_balance
    $setting->Date_of_balance = $setting->value;

    $setting->save();

    return redirect()->back()->with('success', 'Balance updated successfully!');
}
  public function getSalesData(Request $request)
    {
        $printed = Sale::where('printed', 1)->orderBy('created_at', 'desc')->get();
        $unprinted = Sale::where('printed', 0)->orderBy('created_at', 'desc')->get();
        $all = Sale::orderBy('created_at', 'desc')->get();

        return response()->json([
            'printed'   => $printed,
            'unprinted' => $unprinted,
            'all'       => $all
        ]);
    }
    
}





