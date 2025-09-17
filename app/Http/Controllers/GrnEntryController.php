<?php

namespace App\Http\Controllers;

use App\Models\GrnEntry;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\Sale;
use App\Models\SalesHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\GrnEntry2;

class GrnEntryController extends Controller
{
    public function index()
    {
        $entries = GrnEntry::latest()->get();
        return view('dashboard.grn.index', compact('entries'));
    }

    public function create()
    {
        $items = Item::all();
        $suppliers = Supplier::all();
         $entries = GrnEntry::latest()->get();
         $notChangingGRNs = GrnEntry::where('grn_status', 'Not Changing')->get();
        return view('dashboard.grn.create', compact('items', 'suppliers','entries','notChangingGRNs'));
    }
public function store(Request $request)
{
    // 1. Validate the incoming request (all fields optional, status required)
    $request->validate([
        'item_code'     => 'nullable|string',
        'supplier_name' => 'nullable|string|max:255',
        'packs'         => 'nullable|integer',
        'weight'        => 'nullable|numeric',
        'txn_date'      => 'nullable|date',
        'grn_no'        => 'nullable|string',
        'warehouse_no'  => 'nullable|string',
        'total_grn'     => 'nullable|numeric|min:0',
        'per_kg_price'  => 'nullable|numeric|min:0',
        'wasted_weight' => 'nullable|numeric|min:0',
        'wasted_packs'  => 'nullable|numeric|min:0',
        
    ]);

    // 2. Fetch item if provided
    $item = null;
    if ($request->filled('item_code')) {
        $item = Item::where('no', $request->item_code)->first();
        if (!$item) {
            return back()->withErrors(['item_code' => 'Invalid item selected.']);
        }
    }

    // 3. Find or create the supplier using the entered name as code & name
    $supplierName = $request->input('supplier_name', '');
    $supplier = Supplier::firstOrCreate(
        ['code' => $supplierName],
        ['name' => $supplierName] // ✅ save supplier name too
    );
    $supplierCode = $supplier->code;

    // 4. Auto generate auto_purchase_no (based on ID)
    $last = GrnEntry::latest()->first();
    $autoNo = $last ? $last->id + 1 : 1;
    $autoPurchaseNo = str_pad($autoNo, 4, '0', STR_PAD_LEFT);

    // 5. Sequential number logic
    $lastGrnEntry = GrnEntry::orderBy('sequence_no', 'desc')->first();
    $nextSequentialNumber = $lastGrnEntry ? $lastGrnEntry->sequence_no + 1 : 1000;

    // 6. Build code string
    $itemCode = $item ? $item->no : 'ITEM';
    $code = strtoupper($itemCode . '-' . $supplierCode . '-' . $nextSequentialNumber);

    // 7. Calculate total wasted weight value
    $wastedWeight = $request->input('wasted_weight', 0);
    $perKgPrice = $request->input('per_kg_price', 0);
    $totalWastedWeightValue = $wastedWeight * $perKgPrice;

    // 8. Create GRN entry
    GrnEntry::create([
        'auto_purchase_no'    => $autoPurchaseNo,
        'code'                => $code,
        'supplier_code'       => strtoupper($supplierCode),
        'item_code'           => $request->input('item_code', null),
        'item_name'           => $item ? $item->type : null,
        'packs'               => $request->input('packs', null),
        'weight'              => $request->input('weight', null),
        'txn_date'            => $request->input('txn_date', null),
        'grn_no'              => $request->input('grn_no', null),
        'warehouse_no'        => $request->input('warehouse_no', null),
        'original_packs'      => $request->input('packs', null),
        'original_weight'     => $request->input('weight', null),
        'sequence_no'         => $nextSequentialNumber,
        'total_grn'           => $request->input('total_grn', null),
        'per_kg_price'        => $perKgPrice,   // ✅ fixed column name
        'wasted_packs'        => $request->input('wasted_packs', 0),
        'wasted_weight'       => $wastedWeight,
        'total_wasted_weight' => $totalWastedWeightValue,
       
    ]);

    // 9. Redirect with success
    return redirect()->route('grn.create')->with('success', 'GRN Entry added successfully.');
}
public function store2(Request $request)
{
    $validated = $request->validate([
        'code' => 'required|exists:grn_entries,id',
        'packs' => 'required|numeric|min:1',
        'weight' => 'required|numeric|min:0.01',
    ]);

    // Find the existing GRN entry
    $grn = GrnEntry::find($request->code);

    // Update GrnEntry by adding the new packs and weight
    $grn->packs += $request->packs;
    $grn->weight += $request->weight;
    $grn->original_packs += $request->packs;
    $grn->original_weight += $request->weight;
    $grn->save();

    // Fetch the date from Setting
    $settingDate = \App\Models\Setting::value('value');
    $formattedDate = \Carbon\Carbon::parse($settingDate)->format('Y-m-d');

    // Also insert into GrnEntry2 as backup / history
    GrnEntry2::create([
        'code' => $grn->code,
        'supplier_code' => $grn->supplier_code,
        'item_code' => $grn->item_code,
        'item_name' => $grn->item_name,
        'packs' => $request->packs,
        'weight' => $request->weight,
        'txn_date' => $formattedDate,  // <-- use setting date here
        'grn_no' => $grn->grn_no,
    ]);

    return redirect()->back()->with('success', 'GRN updated successfully!');
}

    public function edit($id)
    {
        $entry = GrnEntry::findOrFail($id);
        $items = Item::all();
        $suppliers = Supplier::all();

        return view('dashboard.grn.edit', compact('entry', 'items', 'suppliers'));
    }
  public function update(Request $request, $id)
{
    $request->validate([
        'item_code' => 'required',
        'item_name' => 'required|string',
        'supplier_code' => 'required',
        'packs' => 'required|integer|min:1',
        'weight' => 'required|numeric|min:0.01',
        'txn_date' => 'required|date',
        'grn_no' => 'required|string',
        'warehouse_no' => 'required|string',
        'total_grn' => 'nullable|numeric|min:0',
        'per_kg_price' => 'nullable|numeric|min:0',
    ]);

    $entry = GrnEntry::findOrFail($id);

    $updateData = [
        'item_code' => $request->item_code,
        'item_name' => $request->item_name,
        'supplier_code' => $request->supplier_code,
        'packs' => $request->packs,
        'weight' => $request->weight,
        'original_packs' => $request->packs,
        'original_weight' => $request->weight,
        'sequence_no' => $request->sequence_no,
        'txn_date' => $request->txn_date,
        'grn_no' => $request->grn_no,
        'warehouse_no' => $request->warehouse_no,
    ];

    if ($request->filled('total_grn')) {
        $updateData['total_grn'] = $request->total_grn;
    }

    if ($request->filled('per_kg_price')) {
        $updateData['PerKGPrice'] = $request->per_kg_price;
    }

    $entry->update($updateData);

    // 🔹 Update matching Sale rows
    if ($request->filled('per_kg_price') && !empty($entry->code)) {
        $newPerKgPrice = $request->per_kg_price;

        Sale::where('code', $entry->code)->get()->each(function ($sale) use ($newPerKgPrice) {
            $sale->PerKGPrice = $newPerKgPrice;
            $sale->PerKGTotal = $sale->weight * $newPerKgPrice;
            $sale->SellingKGTotal = $sale->total - $sale->PerKGTotal;
            $sale->save();
        });
    }

    // 🔹 Call your stock recalculation method after update
    $this->updateGrnRemainingStock();

    return redirect()->route('grn.create')->with('success', 'Entry updated successfully.');
}
    public function destroy($id)
    {
        $entry = GrnEntry::findOrFail($id);
        $entry->delete();

        return redirect()->route('grn.create')->with('success', 'Entry deleted.');
    }
    public function getGrnEntryByCode($code)
    {
        $grnEntry = GrnEntry::where('code', $code)->first();

        if ($grnEntry) {
            return response()->json($grnEntry);
        }

        return response()->json(['error' => 'GRN Entry not found.'], 404);
    }
    public function getUsedData($code)
    {
        $usedWeight = DB::table('grn_entries')
            ->where('code', $code)
            ->sum('weight');

        $usedPacks = DB::table('grn_entries')
            ->where('code', $code)
            ->sum('packs');

        return response()->json([
            'used_weight' => $usedWeight,
            'used_packs' => $usedPacks
        ]);
    }
    public function hide($id)
    {
        $entry = GrnEntry::findOrFail($id);
        $entry->is_hidden = true;
        $entry->save();

        return response()->json(['status' => 'hidden']);
    }

    public function unhide($id)
    {
        $entry = GrnEntry::findOrFail($id);
        $entry->is_hidden = false;
        $entry->save();

        return response()->json(['status' => 'unhidden']);
    }
    public function Damagestore(Request $request)
    {
        // 1. Validate the incoming request data
        $validatedData = $request->validate([
            'wasted_code' => 'required|string',
            'wasted_packs' => 'required|numeric|min:0',
            'wasted_weight' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // 2. Find the corresponding GrnEntry record using the `wasted_code`
            $grnEntry = GrnEntry::where('code', $validatedData['wasted_code'])->first();

            if (!$grnEntry) {
                DB::rollBack();
                return redirect()->back()->with('error', 'GRN entry with the provided code not found!');
            }

            // 3. Deduct the wasted packs and weight
            $grnEntry->packs -= $validatedData['wasted_packs'];
            $grnEntry->weight -= $validatedData['wasted_weight'];

            // 4. Add wasted values to the wasted_packs and wasted_weight columns
            $grnEntry->wasted_packs = ($grnEntry->wasted_packs ?? 0) + $validatedData['wasted_packs'];
            $grnEntry->wasted_weight = ($grnEntry->wasted_weight ?? 0) + $validatedData['wasted_weight'];

            // Prevent negative values
            if ($grnEntry->packs < 0 || $grnEntry->weight < 0) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Deduction would result in a negative value. Please check the amounts.');
            }

            // Save the updated GrnEntry record
            $grnEntry->save();

            DB::commit();

            return redirect()->back()->with('success', 'Wasted stock recorded and deducted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'An error occurred while processing the request.');
        }
    }
  public function showSalesBillSummary(Request $request)
    {
        Log::info('Sales Report generation started.');

        // 1. Fetch sales data based on your criteria
        // Add your filtering logic here if needed, e.g., for a specific date range.
        $salesData = SalesBill::with('items')->get();

        if ($salesData->isEmpty()) {
            Log::warning('No sales data found for the report.');
            return view('reports.sales_bill_summary', [
                'salesByBill' => collect(),
                'grnPrices' => collect(),
            ]);
        }

        // Group sales items by bill number
        $salesByBill = $salesData->groupBy('bill_no');

        // 2. Get all unique item codes from the sales data
        $itemCodes = $salesByBill->flatten()->pluck('code')->unique()->map(function ($code) {
            return trim($code);
        })->toArray();

        Log::debug('Unique Item Codes found in sales:', $itemCodes);

        // 3. Fetch the latest GRN price for each item code.
        $grnPrices = DB::table('grn_entries as t1')
            ->select('t1.code', 't1.PerKGPrice')
            ->join(DB::raw('(SELECT code, MAX(created_at) as max_date FROM grn_entries GROUP BY code) as t2'), function($join) {
                $join->on('t1.code', '=', 't2.code')
                     ->on('t1.created_at', '=', 't2.max_date');
            })
            ->whereIn(DB::raw('TRIM(t1.code)'), $itemCodes)
            ->pluck('PerKGPrice', 'code')
            ->toArray();

        // 4. Clean and log the fetched GRN prices
        $grnPrices = array_change_key_case(array_map('trim', $grnPrices));
        
        Log::info('GRN Prices fetched for comparison:', $grnPrices);

        // **Debugging a specific case:**
        // Let's inspect the data for the item 'ALA-NET1-1000' and its prices.
        $specificItemCode = 'ALA-NET1-1000';
        $grnPriceForDebug = $grnPrices[$specificItemCode] ?? null;
        
        Log::debug('Debugging specific item:', [
            'item_code' => $specificItemCode,
            'grn_price_from_db' => $grnPriceForDebug
        ]);
        
        // Use `dd()` to halt execution and inspect data
        // For example, if you want to see the exact GRN prices array before the view is rendered:
        // dd($grnPrices); 

        // 5. Pass the data to the view
        return view('reports.sales_bill_summary', [
            'salesByBill' => $salesByBill,
            'grnPrices' => $grnPrices,
        ]);
    }
    public function updateStatus(Request $request, $id)
{
    $entry = GrnEntry::findOrFail($id);

    if ($request->has('is_hidden')) {
        $entry->is_hidden = $request->is_hidden;
    }

    if ($request->has('show_status')) {
        $entry->show_status = $request->show_status;
    }

    $entry->save();

    return response()->json(['success' => true]);
}
// GrnEntryController.php
// GrnEntryController.php
public function getGrnEntry($code)
{
    $entry = GrnEntry::where('code', $code)->where('show_status', 1)->first();

    if ($entry) {
        return response()->json([
            'per_kg_price' => $entry->PerKGPrice,
        ]);
    }

    return response()->json(['per_kg_price' => null]);
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
       public function showupdateform() {
        $notChangingGRNs = GrnEntry::all();
        $grnEntries = GrnEntry2::all();
        return view('dashboard.grn.updateform', compact('notChangingGRNs', 'grnEntries'));
    }







}

