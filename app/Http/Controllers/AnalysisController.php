<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use App\Services\BcsCalculationService;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class AnalysisController extends Controller
{
    public function __construct(
        private BcsCalculationService $bcsService,
        private GeminiService         $geminiService,
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
            'pet_id'         => 'required|integer|exists:pets,id',
            'weight_kg'      => 'required|numeric|min:0.1|max:200',
            'activity_level' => 'required|in:low,average,high',
        ], [
            'pet_id.required'         => 'Data hewan tidak ditemukan.',
            'pet_id.exists'           => 'Data hewan tidak valid.',
            'weight_kg.required'      => 'Berat badan wajib diisi.',
            'weight_kg.numeric'       => 'Berat badan harus berupa angka.',
            'weight_kg.min'           => 'Berat badan minimal 0.1 kg.',
            'weight_kg.max'           => 'Berat badan maksimal 200 kg.',
            'activity_level.required' => 'Tingkat aktivitas wajib dipilih.',
            'activity_level.in'       => 'Tingkat aktivitas tidak valid.',
        ]);

        // Ownership is enforced via the user's pets relation.
        $pet = $request->user()->pets()->with('breed')->findOrFail($validated['pet_id']);

        $weight   = (float) $validated['weight_kg'];
        $age      = $pet->currentAgeYears();
        $activity = $validated['activity_level'];
        $species  = (string) $pet->breed->species;

        $idealWeight = $pet->breed->getIdealWeightForGender($pet->gender);

        $bcsScore    = $this->bcsService->calculateBcs($weight, $idealWeight);
        $bcsCategory = $this->bcsService->resolveBcsCategory($bcsScore);
        $rer         = $this->bcsService->calculateRer($weight);
        $mer         = $this->bcsService->calculateMer($rer, $species, $age, (bool) $pet->is_neutered, $activity);

        // Gemini recommendation (non-critical).
        $recommendation = null;
        try {
            $recommendation = $this->geminiService->generateRecommendation([
                'species'        => $species,
                'breed'          => $pet->breed->name,
                'gender'         => $pet->gender,
                'age_years'      => $age,
                'weight_kg'      => $weight,
                'ideal_weight'   => $idealWeight,
                'is_neutered'    => (bool) $pet->is_neutered,
                'activity_level' => $activity,
                'bcs_score'      => $bcsScore,
                'bcs_category'   => $bcsCategory,
                'rer'            => $rer,
                'mer'            => $mer,
            ]);
        } catch (Throwable) {
            // Recommendation is non-critical; proceed without it.
        }

        $analysis = DB::transaction(fn () => Analysis::create([
            'user_id'                  => $request->user()->id,
            'pet_id'                   => $pet->id,
            'weight_kg'                => $weight,
            'age_years'                => $age,
            'activity_level'           => $activity,
            'gender'                   => $pet->gender,
            'breed_prediction'         => $pet->breed->name,
            'confidence_score'         => $pet->breed_confidence ?? 0,
            'ideal_weight_used'        => $idealWeight,
            'bcs_score'                => $bcsScore,
            'bcs_category'             => $bcsCategory,
            'rer'                      => $rer,
            'mer'                      => $mer,
            'nutrition_recommendation' => $recommendation,
        ]));

        return response()->json([
            'message' => 'Analisis berhasil.',
            'data'    => $analysis->load('pet'),
        ], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $analysis = Analysis::where('user_id', $request->user()->id)->findOrFail($id);
        $analysis->delete();

        return response()->json(['message' => 'Analisis berhasil dihapus.']);
    }
}
