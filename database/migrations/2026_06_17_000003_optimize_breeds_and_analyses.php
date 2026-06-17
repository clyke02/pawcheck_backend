<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add index + unique constraint on breeds for faster lookup
        Schema::table('breeds', function (Blueprint $table) {
            $table->index('name', 'breeds_name_index');
            $table->unique(['name', 'species'], 'breeds_name_species_unique');
        });

        // Enforce gender as enum instead of plain string
        Schema::table('analyses', function (Blueprint $table) {
            $table->enum('gender', ['male', 'female'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('breeds', function (Blueprint $table) {
            $table->dropUnique('breeds_name_species_unique');
            $table->dropIndex('breeds_name_index');
        });

        Schema::table('analyses', function (Blueprint $table) {
            $table->string('gender')->change();
        });
    }
};
