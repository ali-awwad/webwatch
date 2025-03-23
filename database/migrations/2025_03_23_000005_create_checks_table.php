<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checks', function (Blueprint $table) {
            $table->id();
            $table->string('status')->nullable();
            $table->foreignId('variation_id')->nullable()->constrained()->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->integer('number_of_retries')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checks');
    }
}; 