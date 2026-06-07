<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Analysis extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'pet_id',
        'image_url',
        'weight_kg',
        'age_years',
        'gender',
        'breed_prediction',
        'confidence_score',
        'ideal_weight_used',
        'bcs_score',
        'bcs_category',
        'rer',
        'mer',
        'nutrition_recommendation',
    ];

    protected function casts(): array
    {
        return [
            'weight_kg' => 'decimal:2',
            'age_years' => 'decimal:2',
            'confidence_score' => 'decimal:4',
            'ideal_weight_used' => 'decimal:2',
            'bcs_score' => 'integer',
            'rer' => 'decimal:2',
            'mer' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
