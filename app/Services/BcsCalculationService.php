<?php

namespace App\Services;

class BcsCalculationService
{
    public function calculateBcs(float $weightKg, float $idealWeight): int
    {
        $ratio = $weightKg / $idealWeight;

        return match (true) {
            $ratio < 0.60              => 1,
            $ratio < 0.80              => 2,
            $ratio <= 1.20             => 3,
            $ratio <= 1.40             => 4,
            default                    => 5,
        };
    }

    public function resolveBcsCategory(int $bcsScore): string
    {
        return match ($bcsScore) {
            1 => 'Sangat Kurus',
            2 => 'Kurus',
            3 => 'Ideal',
            4 => 'Gemuk',
            5 => 'Obesitas',
            default => 'Tidak Diketahui',
        };
    }

    public function calculateRer(float $weightKg): float
    {
        return round(70 * pow($weightKg, 0.75), 2);
    }

    public function calculateMer(float $rer, string $gender, float $ageYears): float
    {
        // Senior: >= 7 years, Young intact: < 2 years female or < 1.5 years male
        $factor = match (true) {
            $ageYears >= 7.0 => 1.2,
            $ageYears < 2.0  => 1.8,
            default          => 1.6,
        };

        return round($rer * $factor, 2);
    }
}
