<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Under the new flow an analysis always belongs to a pet; remove orphans.
        DB::table('analyses')->whereNull('pet_id')->delete();

        Schema::table('analyses', function (Blueprint $table) {
            $table->dropForeign(['pet_id']);
        });

        Schema::table('analyses', function (Blueprint $table) {
            $table->dropColumn(['pet_name', 'image_url']);
            $table->enum('activity_level', ['inactive', 'active'])
                  ->default('active')->after('age_years');
        });

        Schema::table('analyses', function (Blueprint $table) {
            $table->foreignId('pet_id')->nullable(false)->change();
        });

        Schema::table('analyses', function (Blueprint $table) {
            $table->foreign('pet_id')->references('id')->on('pets')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('analyses', function (Blueprint $table) {
            $table->dropForeign(['pet_id']);
        });

        Schema::table('analyses', function (Blueprint $table) {
            $table->dropColumn('activity_level');
            $table->string('pet_name', 100)->nullable()->after('pet_id');
            $table->string('image_url')->nullable()->after('pet_name');
            $table->foreignId('pet_id')->nullable()->change();
        });

        Schema::table('analyses', function (Blueprint $table) {
            $table->foreign('pet_id')->references('id')->on('pets')->nullOnDelete();
        });
    }
};
