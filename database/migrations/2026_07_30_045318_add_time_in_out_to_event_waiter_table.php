<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('event_waiter', function (Blueprint $table) {
            $table->timestamp('time_in')->nullable()->after('confirmed_at');
            $table->timestamp('time_out')->nullable()->after('time_in');
        });
    }

    public function down(): void
    {
        Schema::table('event_waiter', function (Blueprint $table) {
            $table->dropColumn(['time_in', 'time_out']);
        });
    }
};