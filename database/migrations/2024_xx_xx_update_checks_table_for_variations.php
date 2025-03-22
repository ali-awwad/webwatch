<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checks', function (Blueprint $table) {
            // Add variation_id column
            $table->foreignId('variation_id')->nullable()->after('website_id')->constrained()->nullOnDelete();
            
            // Copy data from existing website_id to appropriate variations (if needed)
            // This would need to be handled in a seeder or custom migration code
            
            // Then drop the website_id column
            $table->dropForeign(['website_id']);
            $table->dropColumn('website_id');
        });
    }

    public function down(): void
    {
        Schema::table('checks', function (Blueprint $table) {
            // Add website_id back
            $table->foreignId('website_id')->nullable()->after('id');
            
            // Restore data if needed (would need custom code)
            
            // Drop variation_id
            $table->dropForeign(['variation_id']);
            $table->dropColumn('variation_id');
            
            // Re-add foreign key constraint
            $table->foreign('website_id')->references('id')->on('websites')->cascadeOnDelete();
        });
    }
}; 