<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            $table->string('image_url')->nullable()->after('gender');
            $table->decimal('breed_confidence', 5, 4)->nullable()->after('image_url');
            $table->boolean('is_neutered')->default(false)->after('breed_confidence');
            $table->date('birth_date')->nullable()->after('is_neutered');
            $table->decimal('age_at_registration', 4, 2)->nullable()->after('birth_date');
        });
    }

    public function down(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            $table->dropColumn([
                'image_url', 'breed_confidence', 'is_neutered',
                'birth_date', 'age_at_registration',
            ]);
        });
    }
};
