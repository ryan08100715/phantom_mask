<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_purchase_histories', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id');
            $table->string('pharmacy_name', 50)->comment('藥局名稱');
            $table->string('mask_name', 100)->comment('口罩名稱');
            $table->decimal('transaction_amount')->comment('交易金額');
            $table->unsignedInteger('transaction_quantity')->comment('交易數量');
            $table->timestamp('transaction_datetime')->comment('交易日期');

            $table->index('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->comment('使用者購買紀錄');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_purchase_histories');
    }
};
