<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomersLoan extends Model
{
    protected $fillable = [
        'customer_id', 'loan_type', 'settling_way',
        'bill_no', 'description', 'amount',
        'cheque_no', 'bank', 'cheque_date','customer_short_name','Date'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function incomeExpenses()
{
    return $this->hasMany(IncomeExpenses::class, 'loan_id');
}

}