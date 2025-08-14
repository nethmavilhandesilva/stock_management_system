<?php

namespace App\Http\Controllers;

use App\Models\GrnEntry;
use App\Models\Item;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        return view('dashboard.grn.create', compact('items', 'suppliers'));
    }
public function store(Request $request)
{
    // 1. Validate the incoming request
    $request->validate([
        'item_code'       => 'required',
        'supplier_code'   => 'required',
        'packs'           => 'required|integer|min:1',
        'weight'          => 'required|numeric|min:0.01',
        'txn_date'        => 'required|date',
        'grn_no'          => 'nullable|string',
        'warehouse_no'    => 'nullable|string',
        'total_grn'       => 'required|numeric|min:0',
        'per_kg_price'    => 'required|numeric|min:0',
        'wasted_weight'   => 'nullable|numeric|min:0', // optional
        'wasted_packs'    => 'nullable|numeric|min:0', // optional
    ]);

    // 2. Fetch item
    $item = Item::where('no', $request->item_code)->first();
    if (!$item) {
        return back()->withErrors(['item_code' => 'Invalid item selected.']);
    }

    // 3. Fetch supplier
    $supplier = Supplier::where('code', $request->supplier_code)->first();
    if (!$supplier) {
        return back()->withErrors(['supplier_code' => 'Invalid supplier selected.']);
    }

    // 4. Auto generate auto_purchase_no
    $last = GrnEntry::latest()->first();
    $autoNo = $last ? $last->id + 1 : 1;
    $autoPurchaseNo = str_pad($autoNo, 4, '0', STR_PAD_LEFT);

    // 5. Sequential number logic
    $lastGrnEntry = GrnEntry::orderBy('sequence_no', 'desc')->first();
    $nextSequentialNumber = $lastGrnEntry ? $lastGrnEntry->sequence_no + 1 : 1000;

    // 6. Build code string
    $itemTypePrefix     = substr($item->no, 0, 3);
    $supplierNamePrefix = substr($supplier->code, 0, 3);
    $code = $itemTypePrefix . '-' . $supplierNamePrefix . '-' . $nextSequentialNumber;

    // 7. Calculate total wasted weight
    $wastedWeight = $request->input('wasted_weight', 0);
    $perKgPrice   = $request->input('per_kg_price', 0);
    $totalWastedWeightValue = $wastedWeight * $perKgPrice;

    // 8. Create GRN entry
    GrnEntry::create([
        'auto_purchase_no'   => $autoPurchaseNo,
        'code'               => $code,
        'supplier_code'      => $request->supplier_code,
        'item_code'          => $request->item_code,
        'item_name'          => $item->type,
        'packs'              => $request->packs,
        'weight'             => $request->weight,
        'txn_date'           => $request->txn_date,
        'grn_no'             => $request->grn_no,
        'warehouse_no'       => $request->warehouse_no,
        'original_packs'     => $request->packs,
        'original_weight'    => $request->weight,
        'sequence_no'        => $nextSequentialNumber,
        'total_grn'          => $request->total_grn,
        'PerKGPrice'         => $perKgPrice,
        'wasted_packs'       => $request->input('wasted_packs', 0),
        'wasted_weight'      => $wastedWeight,
        'total_wasted_weight'=> $totalWastedWeightValue
    ]);

    // 9. Redirect with success
    return redirect()
        ->route('grn.index')
        ->with('success', 'GRN Entry added successfully.');
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
        'txn_date' => $request->txn_date,
        'grn_no' => $request->grn_no,
        'warehouse_no' => $request->warehouse_no,
    ];

    // Only update password-protected fields if password is entered
    if($request->has('total_grn')) {
        $updateData['total_grn'] = $request->total_grn;
    }
    if($request->has('per_kg_price')) {
        $updateData['PerKGPrice'] = $request->per_kg_price;
    }

    $entry->update($updateData);

    return redirect()->route('grn.index')->with('success','Entry updated successfully.');
}


    public function destroy($id)
    {
        $entry = GrnEntry::findOrFail($id);
        $entry->delete();

        return redirect()->route('grn.index')->with('success', 'Entry deleted.');
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
        'wasted_code'   => 'required|string',
        'wasted_packs'  => 'required|numeric|min:0',
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
        $grnEntry->wasted_packs  = ($grnEntry->wasted_packs ?? 0) + $validatedData['wasted_packs'];
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

}

