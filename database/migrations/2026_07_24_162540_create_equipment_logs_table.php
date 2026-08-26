<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('equipment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_equipment_id')->constrained('event_equipment');
            $table->foreignId('logged_by')->constrained('users');
            $table->enum('action', ['allocated', 'returned', 'damaged', 'missing', 'replaced']);
            $table->integer('quantity')->default(1);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_logs');
    }
};