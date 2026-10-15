<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_waiter', function (Blueprint $table) {
            if (!Schema::hasColumn('event_waiter', 'decline_reason')) {
                $table->text('decline_reason')->nullable()->after('confirmation_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('event_waiter', function (Blueprint $table) {
            if (Schema::hasColumn('event_waiter', 'decline_reason')) {
                $table->dropColumn('decline_reason');
            }
        });
    }
};