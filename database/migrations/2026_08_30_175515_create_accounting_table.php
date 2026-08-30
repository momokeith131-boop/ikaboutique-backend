<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->string('type'); // revenue, expense
            $table->string('category'); // sale, subscription, product_cost, shipping, etc.
            $table->decimal('amount', 10, 2);
            $table->string('description')->nullable();
            $table->string('reference')->nullable(); // order_id, invoice_id, etc.
            $table->date('transaction_date');
            $table->string('status')->default('pending'); // pending, completed, cancelled
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting');
    }
};
