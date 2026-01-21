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
        Schema::table('inventories', function (Blueprint $table) {
            $table->unsignedBigInteger('reference_id')->after('store_id');
            $table->date('expiration_date')->nullable()->after('quantity');
            $table->string('batch')->nullable()->after('quantity');
            $table->integer('discount')->nullable()->after('quantity');
            $table->decimal('price', 8,2)->nullable()->after('quantity')->comment('Precio del producto');
            $table->dropColumn('reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn('reference_id');
            $table->dropColumn('price');
            $table->dropColumn('discount');
            $table->string('reference')->nullable()->comment('venta, ajuste, compra');
        });
    }
};
