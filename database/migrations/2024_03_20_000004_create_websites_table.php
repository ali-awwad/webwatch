<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('websites', function (Blueprint $table) {
            $table->id();
            $table->string('domain');
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('last_status')->nullable();
            $table->foreignId('certificate_id')->nullable()->constrained()->nullOnDelete();
            $table->string('developer_team')->nullable();
            $table->string('tech_stack')->nullable();
            $table->text('notes')->nullable();
            $table->string('redirect_to')->nullable();
            $table->boolean('is_waf_enabled')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('websites');
    }
}; 