<?php

namespace App\Http\Controllers;

use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    /**
     * Display a listing of product images
     */
    public function index(Request $request)
    {
        $query = ProductImage::with('product');

        // Filter by product
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $images = $query->orderBy('sort_order')->get();
        
        // Add full URL to images
        $images->each(function ($image) {
            if ($image->image_url) {
                $image->image_url = asset('storage/' . $image->image_url);
            }
        });
        
        if ($images->isEmpty()) {
            $response['message'] = 'Images not found';
            $response['success'] = false;
            return response()->json($response, Response::HTTP_NOT_FOUND);
        }

        $response['success'] = true;
        $response['message'] = 'Images found';
        $response['data'] = $images;
        return response()->json($response, Response::HTTP_OK);
    }

    /**
     * Store a newly created product image
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'image_url' => 'required|file|mimes:jpeg,png,jpg,webp,gif',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Handle image upload
        if ($request->hasFile('image_url')) {
            $image = $request->file('image_url');
            $imageName = time() . '_' . str_replace(' ', '_', $image->getClientOriginalName());
            $imagePath = $image->storeAs('products/images', $imageName, 'public');
            $validated['image_url'] = $imagePath;
        }

        // If sort_order is not provided, set it to the next available number
        if (!isset($validated['sort_order'])) {
            $maxOrder = ProductImage::where('product_id', $validated['product_id'])
                ->max('sort_order');
            $validated['sort_order'] = $maxOrder !== null ? $maxOrder + 1 : 0;
        }

        $image = ProductImage::create($validated);
        
        // Return with full URL
        $imageData = $image->load('product');
        $imageData->image_url = asset('storage/' . $image->image_url);
        
        return response()->json($imageData, 201);
    }

    /**
     * Display the specified product image
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified product image
     */
    public function update(Request $request, $id)
    {
        $image = ProductImage::findOrFail($id);

        $validated = $request->validate([
            'product_id' => 'sometimes|required|exists:products,id',
            'image_url' => 'nullable|file|mimes:jpeg,png,jpg,webp,gif',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Handle image upload
        if ($request->hasFile('image_url')) {
            // Delete old image if exists
            if ($image->image_url && Storage::disk('public')->exists($image->image_url)) {
                Storage::disk('public')->delete($image->image_url);
            }
            
            $uploadedImage = $request->file('image_url');
            $imageName = time() . '_' . str_replace(' ', '_', $uploadedImage->getClientOriginalName());
            $imagePath = $uploadedImage->storeAs('products/images', $imageName, 'public');
            $validated['image_url'] = $imagePath;
        }

        $image->update($validated);
        
        // Return with full URL
        $imageData = $image->load('product');
        if ($image->image_url) {
            $imageData->image_url = asset('storage/' . $image->image_url);
        }
        
        return response()->json($imageData);
    }

    /**
     * Remove the specified product image
     */
    public function destroy($id)
    {
        $image = ProductImage::findOrFail($id);
        
        // Delete image file if exists
        if ($image->image_url && Storage::disk('public')->exists($image->image_url)) {
            Storage::disk('public')->delete($image->image_url);
        }
        
        $image->delete();
        return response()->json(['message' => 'Product image deleted successfully']);
    }

    /**
     * Reorder product images
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'images' => 'required|array',
            'images.*.id' => 'required|exists:product_images,id',
            'images.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['images'] as $imageData) {
            ProductImage::where('id', $imageData['id'])
                ->update(['sort_order' => $imageData['sort_order']]);
        }

        return response()->json(['message' => 'Images reordered successfully']);
    }
}