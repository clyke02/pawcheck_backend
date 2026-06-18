<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use App\Models\Breed;
use App\Services\BcsCalculationService;
use App\Services\BreedPredictionService;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;
use Illuminate\Support\Facades\DB;

class AnalysisController extends Controller
{
    public function __construct(
        private BcsCalculationService  $bcsService,
        private BreedPredictionService $breedService,
        private GeminiService          $geminiService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $analyses = Analysis::where('user_id', $request->user()->id)
            ->with('pet')
            ->latest('created_at')
            ->get();

        return response()->json(['data' => $analyses]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pet_name'   => 'nullable|string|max:100',
            'image'      => 'required|image|max:5120',
            'weight_kg'  => 'required|numeric|min:0.1|max:200',
            'age_years'  => 'required|numeric|min:0|max:30',
            'gender'     => 'required|in:male,female',
        ], [
            'image.required'    => 'Foto hewan wajib diunggah.',
            'image.image'       => 'File harus berupa gambar.',
            'image.max'         => 'Ukuran foto maksimal 5 MB.',
            'weight_kg.required'=> 'Berat badan wajib diisi.',
            'weight_kg.numeric' => 'Berat badan harus berupa angka.',
            'weight_kg.min'     => 'Berat badan minimal 0.1 kg.',
            'weight_kg.max'     => 'Berat badan maksimal 200 kg.',
            'age_years.required'=> 'Usia wajib diisi.',
            'age_years.numeric' => 'Usia harus berupa angka.',
            'age_years.max'     => 'Usia maksimal 30 tahun.',
            'gender.required'   => 'Jenis kelamin wajib dipilih.',
            'gender.in'         => 'Jenis kelamin tidak valid.',
        ]);

        // Store image first; clean up on any subsequent failure
        $path = $request->file('image')->store('analyses', 'public');

        try {
            $imageUrl = Storage::url($path);

            // Predict breed via FastAPI
            $imagePath = Storage::disk('public')->path($path);
            $prediction = $this->breedService->predict($imagePath);

            $breedName  = $prediction['breed'];
            $confidence = $prediction['confidence'];

            // Resolve ideal weight from breeds table
            $breed = Breed::whereRaw('LOWER(REPLACE(name, " ", "_")) = ?', [
                strtolower($breedName)
            ])->first();

            $idealWeight = $breed
                ? $breed->getIdealWeightForGender($validated['gender'])
                : $this->estimateIdealWeight((float) $validated['weight_kg']);

            // BCS calculation
            $bcsScore    = $this->bcsService->calculateBcs((float) $validated['weight_kg'], $idealWeight);
            $bcsCategory = $this->bcsService->resolveBcsCategory($bcsScore);
            $rer         = $this->bcsService->calculateRer((float) $validated['weight_kg']);
            $mer         = $this->bcsService->calculateMer($rer, $validated['gender'], (float) $validated['age_years']);

            // Gemini recommendation (non-critical)
            $recommendation = null;
            try {
                $recommendation = $this->geminiService->generateRecommendation([
                    'species'      => $breed ? (string) $breed->species : 'hewan',
                    'breed'        => $breedName,
                    'gender'       => $validated['gender'],
                    'age_years'    => $validated['age_years'],
                    'weight_kg'    => $validated['weight_kg'],
                    'ideal_weight' => $idealWeight,
                    'bcs_score'    => $bcsScore,
                    'bcs_category' => $bcsCategory,
                    'rer'          => $rer,
                    'mer'          => $mer,
                ]);
            } catch (Throwable) {
                // Recommendation is non-critical; proceed without it
            }

            $analysis = DB::transaction(fn () => Analysis::create([
                'user_id'                  => $request->user()->id,
                'pet_id'                   => null,
                'pet_name'                 => $validated['pet_name'] ?? null,
                'image_url'                => $imageUrl,
                'weight_kg'                => $validated['weight_kg'],
                'age_years'                => $validated['age_years'],
                'gender'                   => $validated['gender'],
                'breed_prediction'         => $breedName,
                'confidence_score'         => $confidence,
                'ideal_weight_used'        => $idealWeight,
                'bcs_score'                => $bcsScore,
                'bcs_category'             => $bcsCategory,
                'rer'                      => $rer,
                'mer'                      => $mer,
                'nutrition_recommendation' => $recommendation,
            ]));

            return response()->json([
                'message' => 'Analisis berhasil.',
                'data'    => $analysis,
            ], 201);

        } catch (Throwable $e) {
            // If anything fails after image upload, remove the orphaned file
            Storage::disk('public')->delete($path);
            throw $e;
        }
    }

    public function reanalyze(Request $request, int $petId): JsonResponse
    {
        $pet = $request->user()->pets()->with('breed')->findOrFail($petId);

        $validated = $request->validate([
            'weight_kg' => 'required|numeric|min:0.1|max:200',
            'age_years' => 'required|numeric|min:0|max:30',
        ], [
            'weight_kg.required' => 'Berat badan wajib diisi.',
            'weight_kg.numeric'  => 'Berat badan harus berupa angka.',
            'weight_kg.min'      => 'Berat badan minimal 0.1 kg.',
            'weight_kg.max'      => 'Berat badan maksimal 200 kg.',
            'age_years.required' => 'Usia wajib diisi.',
            'age_years.numeric'  => 'Usia harus berupa angka.',
            'age_years.max'      => 'Usia maksimal 30 tahun.',
        ]);

        // Re-analysis reuses the breed already known for this pet, so no photo or
        // FastAPI prediction is needed. Photo, breed and confidence are copied from
        // the pet's most recent analysis; only the weight/age-driven scores change.
        $base = $pet->analyses()->first();

        if (! $base) {
            return response()->json(['message' => 'Hewan ini belum memiliki analisis awal.'], 422);
        }

        $weight = (float) $validated['weight_kg'];
        $age    = (float) $validated['age_years'];

        $idealWeight = $pet->breed
            ? $pet->breed->getIdealWeightForGender($pet->gender)
            : $this->estimateIdealWeight($weight);

        $bcsScore    = $this->bcsService->calculateBcs($weight, $idealWeight);
        $bcsCategory = $this->bcsService->resolveBcsCategory($bcsScore);
        $rer         = $this->bcsService->calculateRer($weight);
        $mer         = $this->bcsService->calculateMer($rer, $pet->gender, $age);

        $recommendation = null;
        try {
            $recommendation = $this->geminiService->generateRecommendation([
                'species'      => $pet->breed ? (string) $pet->breed->species : 'hewan',
                'breed'        => $base->breed_prediction,
                'gender'       => $pet->gender,
                'age_years'    => $age,
                'weight_kg'    => $weight,
                'ideal_weight' => $idealWeight,
                'bcs_score'    => $bcsScore,
                'bcs_category' => $bcsCategory,
                'rer'          => $rer,
                'mer'          => $mer,
            ]);
        } catch (Throwable) {
            // Recommendation is non-critical; proceed without it
        }

        $analysis = DB::transaction(fn () => Analysis::create([
            'user_id'                  => $request->user()->id,
            'pet_id'                   => $pet->id,
            'pet_name'                 => $pet->name,
            'image_url'                => $base->image_url,
            'weight_kg'                => $weight,
            'age_years'                => $age,
            'gender'                   => $pet->gender,
            'breed_prediction'         => $base->breed_prediction,
            'confidence_score'         => $base->confidence_score,
            'ideal_weight_used'        => $idealWeight,
            'bcs_score'                => $bcsScore,
            'bcs_category'             => $bcsCategory,
            'rer'                      => $rer,
            'mer'                      => $mer,
            'nutrition_recommendation' => $recommendation,
        ]));

        return response()->json([
            'message' => 'Analisis ulang berhasil.',
            'data'    => $analysis,
        ], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $analysis = Analysis::where('user_id', $request->user()->id)->findOrFail($id);

        // Delete the image file from storage before removing the record
        $imagePath = ltrim(str_replace('/storage', '', parse_url($analysis->image_url, PHP_URL_PATH)), '/');
        Storage::disk('public')->delete($imagePath);

        $analysis->delete();

        return response()->json(['message' => 'Analisis berhasil dihapus.']);
    }

    private function estimateIdealWeight(float $currentWeight): float
    {
        // Fallback: use current weight as ideal when breed is unrecognised
        return round($currentWeight, 2);
    }
}
