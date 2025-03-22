<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add certificate_id to variations table
        Schema::table('variations', function (Blueprint $table) {
            $table->foreignId('certificate_id')->nullable()->constrained()->nullOnDelete()->after('is_main');
        });

        // Step 2: Migrate existing certificate_id values from websites to their main variations
        $websites = DB::table('websites')->whereNotNull('certificate_id')->get();
        foreach ($websites as $website) {
            // Find the main variation for this website and update it
            DB::table('variations')
                ->where('website_id', $website->id)
                ->where('is_main', true)
                ->update(['certificate_id' => $website->certificate_id]);
        }

        // Step 3: Remove certificate_id from websites table
        Schema::table('websites', function (Blueprint $table) {
            $table->dropConstrainedForeignId('certificate_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Add certificate_id back to websites table
        Schema::table('websites', function (Blueprint $table) {
            $table->foreignId('certificate_id')->nullable()->constrained()->nullOnDelete()->after('last_status');
        });

        // Step 2: Migrate certificate_id values back from main variations to websites
        $variations = DB::table('variations')
            ->where('is_main', true)
            ->whereNotNull('certificate_id')
            ->get();

        foreach ($variations as $variation) {
            DB::table('websites')
                ->where('id', $variation->website_id)
                ->update(['certificate_id' => $variation->certificate_id]);
        }

        // Step 3: Remove certificate_id from variations table
        Schema::table('variations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('certificate_id');
        });
    }
};
