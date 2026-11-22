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
        Schema::table('product_colors', function (Blueprint $table) {
            $table->string('mockup_back')->nullable()->after('mockup_image');
        });

        Schema::table('print_areas', function (Blueprint $table) {
            $table->string('side')->default('depan')->after('name');
            $table->string('artwork_file')->nullable()->after('side');
        });

        Schema::create('print_area_conflicts', function (Blueprint $table) {
            $table->foreignId('print_area_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conflicts_with_id')->constrained('print_areas')->cascadeOnDelete();
            $table->primary(['print_area_id', 'conflicts_with_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_area_conflicts');

        Schema::table('print_areas', function (Blueprint $table) {
            $table->dropColumn(['side', 'artwork_file']);
        });

        Schema::table('product_colors', function (Blueprint $table) {
            $table->dropColumn('mockup_back');
        });
    }
};
