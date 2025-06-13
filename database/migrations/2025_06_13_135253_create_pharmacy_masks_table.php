<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_masks', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('pharmacy_id')->comment('藥局ID');
            $table->string('name', 100)->comment('口罩名稱');
            $table->decimal('price', total: 10, places: 2)->comment('口罩價格');
            $table->unsignedInteger('stock_quantity')->comment('庫存數量');
            $table->timestamps();

            $table->index('pharmacy_id');
            if (! $this->isSqlite()) {
                $table->fullText('name');
            }
            $table->index(['pharmacy_id', 'price']);
            $table->foreign('pharmacy_id')->references('id')->on('pharmacies')->cascadeOnUpdate()->cascadeOnDelete();
            $table->comment('藥局口罩');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_masks');
    }

    private function isSqlite(): bool
    {
        return Schema::connection($this->getConnection())
            ->getConnection()
            ->getPdo()
            ->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
    }
};
