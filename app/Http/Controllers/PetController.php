<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use App\Models\Pet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $pets = $request->user()
            ->pets()
            ->with('breed')
            ->latest()
            ->get();

        return response()->json(['data' => $pets]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'analysis_id' => 'required|integer|exists:analyses,id',
        ], [
            'name.required'        => 'Nama hewan wajib diisi.',
            'analysis_id.required' => 'Data analisis tidak ditemukan.',
            'analysis_id.exists'   => 'Data analisis tidak valid.',
        ]);

        $analysis = Analysis::findOrFail($validated['analysis_id']);

        if ($analysis->pet_id !== null) {
            return response()->json(['message' => 'Analisis ini sudah terhubung ke hewan peliharaan.'], 422);
        }

        $breedName = str_replace('_', ' ', $analysis->breed_prediction);
        $breed = \App\Models\Breed::whereRaw('LOWER(name) = LOWER(?)', [$breedName])->first();

        if (! $breed) {
            return response()->json(['message' => 'Ras tidak ditemukan di database.'], 422);
        }

        $pet = Pet::create([
            'user_id'  => $request->user()->id,
            'breed_id' => $breed->id,
            'name'     => $validated['name'],
            'gender'   => $analysis->gender,
        ]);

        $analysis->update(['pet_id' => $pet->id]);

        return response()->json([
            'message' => 'Hewan peliharaan berhasil ditambahkan.',
            'data'    => $pet->load('breed'),
        ], 201);
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
        $pet->delete();

        return response()->json(['message' => 'Hewan peliharaan berhasil dihapus.']);
    }
}
