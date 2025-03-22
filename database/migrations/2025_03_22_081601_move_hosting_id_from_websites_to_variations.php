<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Variation;
use App\Models\Website;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First add hosting_id to variations table
        Schema::table('variations', function (Blueprint $table) {
            $table->foreignId('hosting_id')->nullable()->constrained('hostings');
        });

        // Remove hosting_id from websites table
        Schema::table('websites', function (Blueprint $table) {
            $table->dropForeign(['hosting_id']);
            $table->dropColumn('hosting_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add hosting_id back to websites table
        Schema::table('websites', function (Blueprint $table) {
            $table->foreignId('hosting_id')->nullable()->constrained('hostings');
        });

        // Remove hosting_id from variations table
        Schema::table('variations', function (Blueprint $table) {
            $table->dropForeign(['hosting_id']);
            $table->dropColumn('hosting_id');
        });
    }
};
