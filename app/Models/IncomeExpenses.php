<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomeExpenses extends Model
{
    use HasFactory;

    protected $table = 'income_expenses';

    protected $fillable = [
        'customer_id',
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
    ];
  public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

}
