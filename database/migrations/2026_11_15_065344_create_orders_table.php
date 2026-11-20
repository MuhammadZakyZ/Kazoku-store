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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('design_type', 20);
            $table->string('delivery_method', 20);
            $table->foreignId('courier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shipping_rate_id')->nullable()->constrained()->nullOnDelete();
            $table->string('shipping_destination')->nullable(); // snapshot
            $table->text('shipping_address')->nullable();
            $table->unsignedBigInteger('shipping_cost')->default(0);
            $table->string('payment_method', 20);
            $table->unsignedBigInteger('total')->default(0);
            $table->unsignedBigInteger('dp_amount')->default(0);
            $table->unsignedSmallInteger('revision_count')->default(0);
            $table->unsignedBigInteger('revision_fee')->default(0);
            $table->string('status', 30)->default('baru')->index();
            $table->string('payment_status', 20)->default('belum_bayar');
            $table->string('tracking_no')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
