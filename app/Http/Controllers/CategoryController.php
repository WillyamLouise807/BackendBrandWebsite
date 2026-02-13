<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index()
    {
        $categories = Category::withCount('products')->get();
        
        // Add full URL to images
        $categories->each(function ($category) {
            if ($category->image_url) {
                $category->image_url = asset('storage/' . $category->image_url);
            }
        });
        
        $categories = Category::all();
        if ($categories->isEmpty()) {
            $response['message'] = 'Categories not found';
            $response['success'] = false;
            return response()->json($response, Response::HTTP_NOT_FOUND);
        }

        $response['success'] = true;
        $response['message'] = 'Categories found';
        $response['data'] = $categories;
        return response()->json($response, Response::HTTP_OK);
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:255',
            'image_url' => 'required|image|mimes:jpeg,png,jpg,webp,gif',
        ]);

        // Handle image upload
        if ($request->hasFile('image_url')) {
            $image = $request->file('image_url');
            $imageName = time() . '_' . str_replace(' ', '_', $image->getClientOriginalName());
            $imagePath = $image->storeAs('categories', $imageName, 'public');
            $validated['image_url'] = $imagePath;
        }

        $category = Category::create($validated);
        
        // Return with full URL
        if ($category->image_url) {
            $category->image_url = asset('storage/' . $category->image_url);
        }
        
        return response()->json($category, 201);
    }

    /**
     * Display the specified category
     */
    public function show($id)
    {
        $category = Category::with('products.materials', 'products.images', 'products.sizeImage')
            ->findOrFail($id);
            
        // Add full URL to category image
        if ($category->image_url) {
            $category->image_url = asset('storage/' . $category->image_url);
        }
        
        return response()->json($category);
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'category_name' => 'sometimes|required|string|max:255',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif',
        ]);

        // Handle image upload
        if ($request->hasFile('image_url')) {
            // Delete old image if exists
            if ($category->image_url && Storage::disk('public')->exists($category->image_url)) {
                Storage::disk('public')->delete($category->image_url);
            }
            
            $image = $request->file('image_url');
            $imageName = time() . '_' . str_replace(' ', '_', $image->getClientOriginalName());
            $imagePath = $image->storeAs('categories', $imageName, 'public');
            $validated['image_url'] = $imagePath;
        }

        $category->update($validated);
        
        // Return with full URL
        if ($category->image_url) {
            $category->image_url = asset('storage/' . $category->image_url);
        }
        
        return response()->json($category);
    }

    /**
     * Remove the specified category
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        
        // Delete image file if exists
        if ($category->image_url && Storage::disk('public')->exists($category->image_url)) {
            Storage::disk('public')->delete($category->image_url);
        }
        
        $category->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }
}