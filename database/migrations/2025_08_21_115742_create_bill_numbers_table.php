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
        Schema::create('bill_numbers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('last_bill_no')->default(1000); // start from 1000
            $table->timestamps();
        });

        // Insert initial row
        DB::table('bill_numbers')->insert([
            'last_bill_no' => 1000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_numbers');
    }
};
