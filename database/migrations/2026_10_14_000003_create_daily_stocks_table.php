<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->integer('quantity')->default(0);
            $table->integer('minimum_stock')->default(0);
            $table->timestamps();

            $table->unique(['menu_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_stocks');
    }
};
