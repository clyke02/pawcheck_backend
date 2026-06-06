<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BreedPredictionService
{
    private string $fastapiUrl;

    public function __construct()
    {
        $this->fastapiUrl = rtrim(config('services.fastapi.url'), '/');
    }

    public function predict(string $imagePath): array
    {
        try {
            $response = Http::timeout(30)->attach(
                'file',
                file_get_contents($imagePath),
                basename($imagePath)
            )->post("{$this->fastapiUrl}/predict/breed");

            if ($response->failed()) {
                throw new RuntimeException('Breed prediction service returned an error: ' . $response->body());
            }

            $data = $response->json();

            return [
                'breed'      => $data['breed'] ?? 'Unknown',
                'confidence' => $data['confidence'] ?? 0.0,
            ];
        } catch (ConnectionException $e) {
            throw new RuntimeException('Cannot connect to breed prediction service: ' . $e->getMessage());
        }
    }
}
