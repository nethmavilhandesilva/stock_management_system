<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     public function up()
    {
        Schema::table('income_expenses', function (Blueprint $table) {
            $table->foreignId('customers_loan_id')
                  ->nullable()
                  ->constrained('customers_loans')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('income_expenses', function (Blueprint $table) {
            $table->dropForeign(['customers_loan_id']);
            $table->dropColumn('customers_loan_id');
        });
    }
};
