<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiService
{
    private string $apiKey;
    private string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    public function generateRecommendation(array $context): string
    {
        $prompt = $this->buildPrompt($context);

        $response = Http::timeout(30)->post("{$this->apiUrl}?key={$this->apiKey}", [
            'contents' => [
                ['parts' => [['text' => $prompt]]],
            ],
            'generationConfig' => [
                'temperature'     => 0.7,
                'maxOutputTokens' => 4096,
            ],
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Gemini API error: ' . $response->body());
        }

        return $response->json('candidates.0.content.parts.0.text') ?? '';
    }

    private function buildPrompt(array $ctx): string
    {
        return <<<EOT
Kamu adalah dokter hewan ahli nutrisi hewan peliharaan. Berikan rekomendasi nutrisi dan perawatan dalam Bahasa Indonesia yang ramah dan mudah dipahami pemilik hewan.

Data Hewan:
- Jenis: {$ctx['species']}
- Ras: {$ctx['breed']}
- Gender: {$ctx['gender']}
- Usia: {$ctx['age_years']} tahun
- Berat: {$ctx['weight_kg']} kg
- Berat ideal: {$ctx['ideal_weight']} kg
- BCS: {$ctx['bcs_score']}/5 ({$ctx['bcs_category']})
- RER: {$ctx['rer']} kkal/hari
- MER: {$ctx['mer']} kkal/hari

Berikan rekomendasi yang mencakup:
1. Penjelasan singkat kondisi BCS hewan
2. Target kalori harian dan frekuensi makan
3. Tips jenis makanan yang sesuai
4. Aktivitas fisik yang dianjurkan
5. Kapan harus konsultasi ke dokter hewan

Format dalam poin-poin yang jelas. Maksimal 300 kata.
EOT;
    }
}
