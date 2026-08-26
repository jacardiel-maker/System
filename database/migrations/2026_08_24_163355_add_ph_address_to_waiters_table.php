<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waiters', function (Blueprint $table) {
            $table->string('region_id', 2)->nullable()->after('address');
            $table->string('province_id', 5)->nullable()->after('region_id');
            $table->string('city_id', 7)->nullable()->after('province_id');
            $table->string('barangay_code', 10)->nullable()->after('city_id');

            $table->index(['region_id', 'province_id', 'city_id']);
        });
    }

    public function down(): void
    {
        Schema::table('waiters', function (Blueprint $table) {
            $table->dropColumn(['region_id', 'province_id', 'city_id', 'barangay_code']);
        });
    }
};