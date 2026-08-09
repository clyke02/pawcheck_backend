<?php

namespace App\Http\Controllers;

use App\Exceptions\NotAPetImageException;
use App\Models\Breed;
use App\Models\Pet;
use App\Services\BreedPredictionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PetController extends Controller
{
    public function __construct(
        private BreedPredictionService $breedService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $pets = $request->user()->pets()->with('breed','analyses')->latest()->get();

        return response()->json(['data' => $pets]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'image'               => 'required|image|max:5120',
            'gender'              => 'required|in:male,female',
            'is_neutered'         => 'required|boolean',
            'birth_date'          => 'nullable|date|before_or_equal:today',
            'age_at_registration' => 'nullable|numeric|min:0|max:30',
        ], [
            'name.required'        => 'Nama hewan wajib diisi.',
            'image.required'       => 'Foto hewan wajib diunggah.',
            'image.image'          => 'File harus berupa gambar.',
            'image.max'            => 'Ukuran foto maksimal 5 MB.',
            'gender.required'      => 'Jenis kelamin wajib dipilih.',
            'gender.in'            => 'Jenis kelamin tidak valid.',
            'is_neutered.required' => 'Status sterilisasi wajib dipilih.',
        ]);

        if (empty($validated['birth_date']) && ($validated['age_at_registration'] ?? null) === null) {
            return response()->json(['message' => 'Tanggal lahir atau perkiraan umur wajib diisi.'], 422);
        }

        // Store image first; clean up on any later failure.
        $path = $request->file('image')->store('pets', 'public');

        try {
            $imagePath  = Storage::disk('public')->path($path);
            $prediction = $this->breedService->predict($imagePath);
            $breedName  = $prediction['breed'];
            $confidence = $prediction['confidence'];

            $breed = Breed::whereRaw('LOWER(REPLACE(name, " ", "_")) = ?', [
                strtolower($breedName)
            ])->first();

            if (! $breed) {
                Storage::disk('public')->delete($path);
                return response()->json([
                    'message' => 'Ras hewan tidak dikenali. Coba gunakan foto yang lebih jelas.',
                ], 422);
            }

            $pet = Pet::create([
                'user_id'             => $request->user()->id,
                'breed_id'            => $breed->id,
                'name'                => $validated['name'],
                'gender'              => $validated['gender'],
                'image_url'           => Storage::url($path),
                'breed_confidence'    => $confidence,
                'is_neutered'         => $validated['is_neutered'],
                'birth_date'          => $validated['birth_date'] ?? null,
                'age_at_registration' => $validated['age_at_registration'] ?? null,
            ]);

            return response()->json([
                'message' => 'Hewan peliharaan berhasil ditambahkan.',
                'data'    => $pet->load('breed'),
            ], 201);
        } catch (NotAPetImageException $e) {
            Storage::disk('public')->delete($path);
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            Storage::disk('public')->delete($path);
            throw $e;
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $pet = $request->user()
            ->pets()
            ->with(['breed', 'analyses'])
            ->findOrFail($id);

        return response()->json(['data' => $pet]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $pet = $request->user()->pets()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ], [
            'name.required' => 'Nama hewan wajib diisi.',
        ]);

        $pet->update($validated);

        return response()->json([
            'message' => 'Nama hewan berhasil diubah.',
            'data'    => $pet,
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $pet = $request->user()->pets()->findOrFail($id);

        // Remove the pet photo from storage. Analyses are removed via cascade.
        if ($pet->image_url) {
            $imagePath = ltrim(str_replace('/storage', '', parse_url($pet->image_url, PHP_URL_PATH)), '/');
            Storage::disk('public')->delete($imagePath);
        }

        $pet->delete();

        return response()->json(['message' => 'Hewan peliharaan berhasil dihapus.']);
    }
}
