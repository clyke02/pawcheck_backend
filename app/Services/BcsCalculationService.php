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

    public function calculateMer(
        float $rer,
        string $species,
        float $ageYears,
        bool $isNeutered,
        string $activityLevel,
    ): float {
        // Faktor pengali MER mengacu tabel Pet Nutrition Alliance (PNA).
        // Prioritas: pertumbuhan -> aktivitas rendah (inaktif) -> status steril.
        $isCat = strtolower($species) === 'cat';

        $factor = match (true) {
            $ageYears < 2.0          => 2.0,                 // Pertumbuhan (< 2 tahun)
            $activityLevel === 'low' => $isCat ? 1.0 : 1.4,  // Inaktif / aktivitas rendah
            ! $isNeutered            => $isCat ? 1.4 : 1.8,  // Dewasa tidak steril
            default                  => $isCat ? 1.2 : 1.6,  // Dewasa steril
        };

        return round($rer * $factor, 2);
    }
}
