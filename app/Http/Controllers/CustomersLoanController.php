<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\CustomersLoan;
use App\Models\IncomeExpenses;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
 // Corrected model name if it was 'CustomersLoan' in your DB

class CustomersLoanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
   public function index(Request $request)
{
    // Fetch all customers for the dropdown/search
    $customers = Customer::all();

    // Start the query to fetch loans with related customers, ordered by latest
    $query = CustomersLoan::with('customer')->latest();

    // If a filter_customer query param exists and is not empty, filter the loans by that customer
    if ($request->filled('filter_customer')) {
        $query->where('customer_id', $request->filter_customer);
    }

    // Execute the query to get the loans
    $loans = $query->get();

    // Return the view with customers and filtered loans
    return view('dashboard.customers_loans.index', compact('customers', 'loans'));
}
public function store(Request $request)
{
    // Base validation rules
    $rules = [
        'loan_type' => 'required|string|in:old,today,ingoing,outgoing',
        'settling_way' => 'nullable|string|in:cash,cheque',
        'customer_id' => 'nullable|exists:customers,id',
        'amount' => 'required|numeric|min:0.01',
        'description' => 'required|string|max:255',
        'bill_no' => 'nullable|string|max:255',
        'cheque_no' => 'nullable|string|max:255',
        'bank' => 'nullable|string|max:255',
        'cheque_date' => 'nullable|date',
    ];

    // Conditional required fields based on loan type and settling way
    if ($request->input('loan_type') === 'ingoing' || $request->input('loan_type') === 'outgoing') {
        $rules['customer_id'] = 'nullable';
        $rules['settling_way'] = 'nullable';
        $rules['bill_no'] = 'nullable';
        $rules['cheque_no'] = 'nullable';
        $rules['bank'] = 'nullable';
        $rules['cheque_date'] = 'nullable';
    } elseif ($request->input('settling_way') === 'cheque') {
        $rules['cheque_no'] = 'required|string|max:255';
        $rules['bank'] = 'required|string|max:255';
        $rules['cheque_date'] = 'required|date';
        $rules['bill_no'] = 'nullable';
        $rules['customer_id'] = 'required|exists:customers,id';
    } else { // Handles loan_type 'old' and 'today' with 'cash' settling_way
        $rules['bill_no'] = 'nullable|string|max:255';
        $rules['cheque_no'] = 'nullable';
        $rules['bank'] = 'nullable';
        $rules['cheque_date'] = 'nullable';
        $rules['customer_id'] = 'required|exists:customers,id';
    }

    $validated = $request->validate($rules);

    // This is the conditional logic to prevent saving 'ingoing'/'outgoing' to CustomersLoan
    if ($validated['loan_type'] !== 'ingoing' && $validated['loan_type'] !== 'outgoing') {
        // Create and save the new CustomersLoan record
        $loan = new CustomersLoan();
        $loan->loan_type = $validated['loan_type'];
        $loan->settling_way = $validated['settling_way'] ?? null;
        $loan->customer_id = $validated['customer_id'];
        $loan->amount = $validated['amount'];
        $loan->description = $validated['description'];

        if ($loan->customer_id) {
            $customer = Customer::find($loan->customer_id);
            $loan->customer_short_name = $customer->short_name;
        } else {
            $loan->customer_short_name = null;
        }

        if (($validated['settling_way'] ?? null) === 'cheque') {
            $loan->cheque_no = $validated['cheque_no'];
            $loan->bank = $validated['bank'];
            $loan->cheque_date = $validated['cheque_date'];
            $loan->bill_no = null;
        } else {
            $loan->bill_no = $validated['bill_no'] ?? null;
            $loan->cheque_no = null;
            $loan->bank = null;
            $loan->cheque_date = null;
        }
        $loan->save();
    }
    
    // Create and save a new IncomeExpenses record for all transactions
    $incomeExpense = new IncomeExpenses();
    $incomeExpense->loan_type = $validated['loan_type'];
    $incomeExpense->customer_id = $validated['customer_id'] ?? null;
    $incomeExpense->description = $validated['description'];
    $incomeExpense->bill_no = $validated['bill_no'] ?? null;
    $incomeExpense->cheque_no = $validated['cheque_no'] ?? null;
    $incomeExpense->bank = $validated['bank'] ?? null;
    $incomeExpense->cheque_date = $validated['cheque_date'] ?? null;
    $incomeExpense->settling_way = $validated['settling_way'] ?? null;
    $incomeExpense->customer_short_name = null;

    // This is the corrected block to handle the potential missing customer_id
    if (isset($validated['customer_id']) && $validated['customer_id']) {
        $customer = Customer::find($validated['customer_id']);
        if ($customer) {
            $incomeExpense->customer_short_name = $customer->short_name;
        }
    }

    // Determine the amount and type based on loan_type
    if ($validated['loan_type'] === 'ingoing') {
        $incomeExpense->amount = $validated['amount'];
        $incomeExpense->type = 'income';
    } elseif ($validated['loan_type'] === 'outgoing' || $validated['loan_type'] === 'today') {
        $incomeExpense->amount = -$validated['amount'];
        $incomeExpense->type = 'expense';
    } elseif ($validated['loan_type'] === 'old') {
        $incomeExpense->amount = $validated['amount'];
        $incomeExpense->type = 'income';
    }
    
    $incomeExpense->save();

    return redirect()->route('customers-loans.index')->with('success', 'Loan and income/expense record created successfully!');
}
    public function destroy(CustomersLoan $loan)
{
    try {
        // Find and delete the corresponding IncomeExpenses record.
        // The where clauses match the fields used during creation to ensure
        // the correct record is targeted for deletion.
        IncomeExpenses::where('loan_type', $loan->loan_type)
                      ->where('customer_id', $loan->customer_id)
                      ->where('amount', $loan->amount)
                      ->where('description', $loan->description)
                      ->delete();
                      
        // Now, delete the CustomersLoan record itself.
        $loan->delete();
        
        return redirect()->back()->with('success', 'Loan and associated income/expense record deleted successfully!');
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to delete loan: ' . $e->getMessage());
    }
}
public function getTotalLoanAmount($customerId)
{
    $oldSum = CustomersLoan::where('customer_id', $customerId)
                ->where('loan_type', 'old')
                ->sum('amount');

    $todaySum = CustomersLoan::where('customer_id', $customerId)
                ->where('loan_type', 'today')
                ->sum('amount');

    if ($todaySum == 0) {
        $totalAmount = $oldSum;
    } else {
        $totalAmount = $todaySum - $oldSum;
    }

    return response()->json(['total_amount' => $totalAmount]);
}

 public function loanReportResults(Request $request)
{
    // Validate password first (optional)
   

    $query = CustomersLoan::query();

    // Filter by customer_short_name if provided
    if ($request->filled('customer_short_name')) {
        $query->where('customer_short_name', $request->customer_short_name);
    }

    // Filter by date range if provided (assuming created_at exists)
    if ($request->filled('start_date')) {
        $query->whereDate('created_at', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
        $query->whereDate('created_at', '<=', $request->end_date);
    }

    $loans = $query->orderBy('created_at', 'desc')->get();

    return view('dashboard.reports.loan-results', compact('loans'));
}
 public function loanReport()
    {
        // 1. Fetch all loan data from the database.
        $allLoans = CustomersLoan::all();

        // 2. Group the loans by customer_short_name to process them individually.
        $groupedLoans = $allLoans->groupBy('customer_short_name');

        $finalLoans = [];

        // 3. Iterate through each customer's loan group to apply the highlighting logic.
        foreach ($groupedLoans as $customerShortName => $loans) {
            // Find the most recent 'old' and 'today' loans for this customer.
            $lastOldLoan = $loans->where('loan_type', 'old')->sortByDesc('created_at')->first();
            $lastTodayLoan = $loans->where('loan_type', 'today')->sortByDesc('created_at')->first();

            $highlightColor = null;

            // 4. Apply the NEW highlighting logic.
            // Condition 1: Check if a today loan exists.
            if ($lastOldLoan && $lastTodayLoan) {
                // Calculate the number of days between the last 'old' loan and the last 'today' loan.
                $daysBetweenLoans = Carbon::parse($lastOldLoan->created_at)->diffInDays(Carbon::parse($lastTodayLoan->created_at));

                // Apply the highlight color based on the time gap between the loans.
                if ($daysBetweenLoans > 30) {
                    $highlightColor = 'red-highlight';
                } elseif ($daysBetweenLoans >= 14 && $daysBetweenLoans <= 30) {
                    $highlightColor = 'blue-highlight';
                }
            }
            // Condition 2: Fallback to the old logic if no 'today' loan exists.
            elseif ($lastOldLoan && !$lastTodayLoan) {
                 $daysSinceLastOldLoan = Carbon::parse($lastOldLoan->created_at)->diffInDays(Carbon::now());
                 if ($daysSinceLastOldLoan > 30) {
                    $highlightColor = 'red-highlight';
                } elseif ($daysSinceLastOldLoan >= 14 && $daysSinceLastOldLoan <= 30) {
                    $highlightColor = 'blue-highlight';
                }
            }


            // 5. Calculate the total amount for the customer.
            $totalAmount = $loans->sum('amount');

            // 6. Create a standardized object to pass to the view.
            $finalLoans[] = (object) [
                'customer_short_name' => $customerShortName,
                'total_amount' => $totalAmount,
                'highlight_color' => $highlightColor,
            ];
        }

        // 7. Return the view with the processed loan data.
        return view('dashboard.reports.loan-report', ['loans' => collect($finalLoans)]);
    }

}