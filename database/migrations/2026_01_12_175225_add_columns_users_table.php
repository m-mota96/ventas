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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable()->after('id');
            $table->integer('status')->default(1)->comment('1: activo, 0: inactivo')->after('remember_token');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('store_id');
            $table->dropColumn('status');
            $table->dropColumn('deleted_at');
        });
    }
};
