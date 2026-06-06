<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Breed extends Model
{
    protected $fillable = ['name', 'species', 'ideal_weight_male', 'ideal_weight_female'];

    protected function casts(): array
    {
        return [
            'ideal_weight_male' => 'decimal:2',
            'ideal_weight_female' => 'decimal:2',
        ];
    }

    public function pets(): HasMany
    {
        return $this->hasMany(Pet::class);
    }

    public function getIdealWeightForGender(string $gender): float
    {
        return $gender === 'male'
            ? (float) $this->ideal_weight_male
            : (float) $this->ideal_weight_female;
    }
}
