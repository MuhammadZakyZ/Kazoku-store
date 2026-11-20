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
        Schema::create('designs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('print_area_id')->nullable()->constrained()->nullOnDelete();
            $table->string('area_name')->nullable(); // snapshot
            $table->string('print_size')->nullable(); // snapshot, mis. 10x10 cm
            $table->unsignedBigInteger('area_price')->default(0); // snapshot
            $table->string('file'); // disk privat
            $table->string('type', 20); // pelanggan | final
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('designs');
    }
};
