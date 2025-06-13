<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacies', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name', 50)->comment('藥局名稱');
            $table->decimal('cash_balance', total: 10, places: 2)->comment('現金餘額');
            $table->timestamps();

            if (! $this->isSqlite()) {
                $table->fullText('name');
            }
            $table->comment('藥局');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacies');
    }

    private function isSqlite(): bool
    {
        return Schema::connection($this->getConnection())
            ->getConnection()
            ->getPdo()
            ->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
    }
};
