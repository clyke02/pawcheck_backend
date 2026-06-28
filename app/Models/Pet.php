<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pet extends Model
{
    protected $fillable = [
        'user_id', 'breed_id', 'name', 'gender', 'image_url',
        'breed_confidence', 'is_neutered', 'birth_date', 'age_at_registration',
    ];

    protected $appends = ['current_age_years'];

    protected function casts(): array
    {
        return [
            'is_neutered'         => 'boolean',
            'birth_date'          => 'date',
            'age_at_registration' => 'decimal:2',
            'breed_confidence'    => 'decimal:4',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function breed(): BelongsTo
    {
        return $this->belongsTo(Breed::class);
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(Analysis::class)->latest('created_at');
    }

    /**
     * Current age in years, derived either from the exact birth date or from
     * the estimated age given at registration plus the elapsed time since.
     */
    public function currentAgeYears(): float
    {
        $now = now();

        if ($this->birth_date) {
            return round($this->birth_date->floatDiffInYears($now), 2);
        }

        if ($this->age_at_registration !== null) {
            $base = $this->created_at ?? $now;
            return round((float) $this->age_at_registration + $base->floatDiffInYears($now), 2);
        }

        return 0.0;
    }

    public function getCurrentAgeYearsAttribute(): float
    {
        return $this->currentAgeYears();
    }
}
