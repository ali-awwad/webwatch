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
            $table->foreignId('developer_team_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->boolean('is_skipped')->default(false);
            $table->boolean('is_waf_enabled')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('websites');
    }
}; 