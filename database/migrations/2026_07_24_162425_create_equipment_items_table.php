<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('equipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('equipment_categories');
            $table->string('name');
            $table->string('serial_code')->unique()->nullable();
            $table->integer('total_quantity')->default(0);
            $table->integer('available_quantity')->default(0);
            $table->enum('condition', ['new', 'good', 'fair', 'damaged'])->default('good');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_items');
    }
};