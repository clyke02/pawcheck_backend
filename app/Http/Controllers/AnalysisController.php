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
            ->latest('created_at')
            ->get();

        return response()->json(['data' => $analyses]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image'      => 'required|image|max:5120',
            'weight_kg'  => 'required|numeric|min:0.1|max:200',
            'age_years'  => 'required|numeric|min:0|max:30',
            'gender'     => 'required|in:male,female',
        ]);

        // Store image
        $path = $request->file('image')->store('analyses', 'public');
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

        // Gemini recommendation
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

        $analysis = Analysis::create([
            'user_id'                 => $request->user()->id,
            'pet_id'                  => null,
            'image_url'               => $imageUrl,
            'weight_kg'               => $validated['weight_kg'],
            'age_years'               => $validated['age_years'],
            'gender'                  => $validated['gender'],
            'breed_prediction'        => $breedName,
            'confidence_score'        => $confidence,
            'ideal_weight_used'       => $idealWeight,
            'bcs_score'               => $bcsScore,
            'bcs_category'            => $bcsCategory,
            'rer'                     => $rer,
            'mer'                     => $mer,
            'nutrition_recommendation' => $recommendation,
        ]);

        return response()->json([
            'message' => 'Analisis berhasil.',
            'data'    => $analysis,
        ], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $analysis = Analysis::where('user_id', $request->user()->id)->findOrFail($id);
        $analysis->delete();

        return response()->json(['message' => 'Analisis berhasil dihapus.']);
    }

    private function estimateIdealWeight(float $currentWeight): float
    {
        // Fallback: use current weight as ideal when breed is unrecognised
        return round($currentWeight, 2);
    }
}
