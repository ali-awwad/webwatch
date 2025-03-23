<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hosting_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('certificate_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_main')->default(false);
            $table->string('redirect_to')->nullable();
            $table->string('notes')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variations');
    }
}; 