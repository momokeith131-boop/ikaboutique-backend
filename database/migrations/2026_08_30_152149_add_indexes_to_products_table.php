<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('name');
            $table->index('category_id');
            $table->index('shop_id');
            $table->index('price');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['category_id']);
            $table->dropIndex(['shop_id']);
            $table->dropIndex(['price']);
            $table->dropIndex(['created_at']);
        });
    }
};
