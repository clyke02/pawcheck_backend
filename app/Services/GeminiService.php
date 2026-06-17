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

        $response = Http::timeout(60)->post("{$this->apiUrl}?key={$this->apiKey}", [
            'contents' => [
                ['parts' => [['text' => $prompt]]],
            ],
            'generationConfig' => [
                'temperature'     => 0.7,
                'maxOutputTokens' => 4096,
                'thinkingConfig'  => ['thinkingBudget' => 0],
            ],
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Gemini API error: ' . $response->body());
        }

        $parts = $response->json('candidates.0.content.parts') ?? [];
        $text  = collect($parts)
            ->filter(fn($p) => empty($p['thought']))
            ->pluck('text')
            ->implode('');

        return $text;
    }

    private function buildPrompt(array $ctx): string
    {
        return <<<EOT
Berikan rekomendasi nutrisi dan perawatan spesifik untuk hewan peliharaan berikut dalam Bahasa Indonesia. Langsung tulis rekomendasinya tanpa salam pembuka, perkenalan diri, atau kalimat pembuka apapun.

Data Hewan:
- Jenis: {$ctx['species']}
- Ras: {$ctx['breed']}
- Gender: {$ctx['gender']}
- Usia: {$ctx['age_years']} tahun
- Berat saat ini: {$ctx['weight_kg']} kg
- Berat ideal: {$ctx['ideal_weight']} kg
- BCS: {$ctx['bcs_score']}/5 ({$ctx['bcs_category']})
- RER (kebutuhan energi istirahat): {$ctx['rer']} kkal/hari
- MER (kebutuhan energi harian): {$ctx['mer']} kkal/hari

Tulis rekomendasi dalam 6 bagian berikut:

1. Kondisi Saat Ini
Jelaskan kondisi tubuh berdasarkan BCS dan selisih berat saat ini vs berat ideal. Apakah hewan perlu menurunkan, menaikkan, atau mempertahankan berat badan, seberapa urgen, dan kemungkinan penyebab non-nutrisi yang perlu disingkirkan terlebih dahulu (seperti parasit, masalah gigi, atau stres) sebelum fokus ke program pakan.

2. Kebutuhan Kalori & Jadwal Makan
Sebutkan target kalori harian berdasarkan MER, frekuensi makan yang dianjurkan, estimasi porsi per makan dalam gram, target kenaikan atau penurunan berat badan yang aman per minggu, dan panduan transisi pakan bertahap (berapa hari untuk menyesuaikan porsi baru agar tidak memicu gangguan pencernaan).

3. Makanan yang Dianjurkan
Berikan rekomendasi spesifik yang sesuai kondisi BCS {$ctx['bcs_score']}/5 dan karakteristik ras {$ctx['breed']}. Sertakan jenis nutrisi yang perlu diprioritaskan (protein, lemak, serat, dll.) beserta contoh tipe produk atau bahan makanan yang cocok.

4. Makanan & Bahan yang Harus Dihindari
Sebutkan minimal 4 makanan atau bahan yang harus dihindari, dengan penekanan pada yang spesifik relevan untuk kondisi BCS {$ctx['bcs_score']}/5 dan kerentanan ras {$ctx['breed']}. Sertakan alasan singkat yang terkait langsung dengan kondisi hewan ini, bukan sekadar daftar pantangan umum.

5. Aktivitas Fisik
Rekomendasikan jenis dan durasi aktivitas yang sesuai dengan usia {$ctx['age_years']} tahun, kondisi BCS saat ini, dan karakter ras {$ctx['breed']}.

6. Pemantauan & Kapan ke Dokter Hewan
Berikan panduan pemantauan mandiri di rumah (frekuensi timbang, cara menilai perkembangan BCS sendiri). Sebutkan juga tanda-tanda spesifik yang mengharuskan pemilik segera membawa hewan ke dokter hewan, terutama terkait kondisi BCS saat ini.

Tulis dengan bahasa yang mudah dipahami pemilik hewan pemula. Maksimal 500 kata. Gunakan format poin per bagian.
EOT;
    }
}
