<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_opening_hours', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('pharmacy_id')->comment('藥局ID');
            $table->enum('day_of_week', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])->comment('星期幾');
            $table->time('start_time')->comment('開始時間');
            $table->time('end_time')->comment('結束時間');
            $table->timestamps();

            $table->index('pharmacy_id');
            $table->index(['pharmacy_id', 'day_of_week', 'start_time', 'end_time'], 'pharmacy_opening_hours_p_dow_st_et');
            $table->foreign('pharmacy_id')->references('id')->on('pharmacies')->cascadeOnUpdate()->cascadeOnDelete();
            $table->comment('藥局營業時間');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_opening_hours');
    }
};
