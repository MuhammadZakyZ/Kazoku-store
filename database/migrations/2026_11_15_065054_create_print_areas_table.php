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
        Schema::create('print_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // posisi sablon
            $table->decimal('width_cm', 6, 1);
            $table->decimal('height_cm', 6, 1);
            $table->unsignedBigInteger('price')->default(0);
            $table->decimal('preview_x', 5, 2)->nullable(); // persen pada mockup
            $table->decimal('preview_y', 5, 2)->nullable();
            $table->decimal('preview_w', 5, 2)->nullable();
            $table->decimal('preview_h', 5, 2)->nullable();

            $table->unique(['product_id', 'name']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_areas');
    }
};
