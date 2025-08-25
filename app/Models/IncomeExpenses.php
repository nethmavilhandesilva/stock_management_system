<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncomeExpenses extends Model
{
    protected $table = 'income_expenses';

    protected $fillable = [
        'customer_id',
        'loan_id', // Added this field to link records
        'loan_type',
        'settling_way',
        'bill_no',
        'description',
        'amount',
        'cheque_no',
        'bank',
        'cheque_date',
        'customer_short_name',
        'unique_code',
        'user_id',
        'Date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function loan()
    {
        return $this->belongsTo(CustomersLoan::class, 'loan_id');
    }
}
