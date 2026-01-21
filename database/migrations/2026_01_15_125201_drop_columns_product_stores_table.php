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
        Schema::table('product_stores', function (Blueprint $table) {
            $table->dropColumn('batch');
            $table->dropColumn('expiration_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_stores', function (Blueprint $table) {
            $table->string('batch')->nullable();
            $table->date('expiration_date')->nullable();
        });
    }
};
