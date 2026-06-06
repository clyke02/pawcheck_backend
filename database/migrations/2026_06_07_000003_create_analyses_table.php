<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pet_id')->nullable()->constrained('pets')->nullOnDelete();
            $table->string('image_url');
            $table->decimal('weight_kg', 5, 2);
            $table->decimal('age_years', 4, 2);
            $table->string('gender');
            $table->string('breed_prediction');
            $table->decimal('confidence_score', 5, 4);
            $table->decimal('ideal_weight_used', 5, 2);
            $table->tinyInteger('bcs_score');
            $table->string('bcs_category');
            $table->decimal('rer', 8, 2);
            $table->decimal('mer', 8, 2);
            $table->text('nutrition_recommendation')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analyses');
    }
};
