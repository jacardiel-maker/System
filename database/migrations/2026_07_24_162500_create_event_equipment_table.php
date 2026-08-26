<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('event_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('equipment_item_id')->constrained('equipment_items');
            $table->foreignId('assigned_waiter_id')->nullable()->constrained('waiters')->nullOnDelete();
            $table->integer('quantity_allocated');
            $table->integer('quantity_returned')->default(0);
            $table->integer('quantity_damaged')->default(0);
            $table->integer('quantity_missing')->default(0);
            $table->enum('status', ['allocated', 'in_use', 'returned', 'partially_returned', 'lost'])->default('allocated');
            $table->text('notes')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_equipment');
    }
};