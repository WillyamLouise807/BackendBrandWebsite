<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MaterialController extends Controller
{
    /**
     * Display a listing of materials
     */
    public function index()
    {
        $materials = Material::withCount('products')->get();
        if ($materials->isEmpty()) {
            $response['message'] = 'Materials not found';
            $response['success'] = false;
            return response()->json($response, Response::HTTP_NOT_FOUND);
        }

        $response['success'] = true;
        $response['message'] = 'Materials found';
        $response['data'] = $materials;
        return response()->json($response, Response::HTTP_OK);
    }

    /**
     * Store a newly created material
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_name' => 'required|string|max:255',
        ]);

        $material = Material::create($validated);
        return response()->json($material, 201);
    }

    /**
     * Display the specified material
     */
    public function show($id)
    {
        $material = Material::with('products')->findOrFail($id);
        return response()->json($material);
    }

    /**
     * Update the specified material
     */
    public function update(Request $request, $id)
    {
        $material = Material::findOrFail($id);

        $validated = $request->validate([
            'material_name' => 'required|string|max:255',
        ]);

        $material->update($validated);
        return response()->json($material);
    }

    /**
     * Remove the specified material
     */
    public function destroy($id)
    {
        $material = Material::findOrFail($id);
        $material->delete();
        return response()->json(['message' => 'Material deleted successfully']);
    }
}