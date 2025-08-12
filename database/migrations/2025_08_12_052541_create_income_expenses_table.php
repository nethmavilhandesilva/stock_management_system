<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('income_expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('loan_type', 100)->nullable();
            $table->string('settling_way', 100)->nullable();
            $table->string('bill_no', 100)->nullable();
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('cheque_no', 100)->nullable();
            $table->string('bank', 150)->nullable();
            $table->date('cheque_date')->nullable();
            $table->string('customer_short_name', 150)->nullable();
            $table->string('unique_code', 150)->nullable()->unique();
            $table->unsignedBigInteger('user_id')->nullable();

            $table->timestamps();

            // Optional foreign keys (uncomment if you have related tables)
            // $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('income_expenses');
    }
};
