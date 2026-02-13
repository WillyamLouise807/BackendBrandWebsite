<?php

namespace App\Http\Controllers;

use App\Models\ProductSizeImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductSizeImageController extends Controller
{
    /**
     * Display the size image for a product
     */
    public function show($productId)
    {
        $sizeImage = ProductSizeImage::where('product_id', $productId)
            ->with('product')
            ->first();
            
        if (!$sizeImage) {
            return response()->json(['message' => 'Size image not found'], 404);
        }
        
        if ($sizeImage->image_url) {
            $sizeImage->image_url = asset('storage/' . $sizeImage->image_url);
        }
        
        return response()->json($sizeImage);
    }

    /**
     * Store or update size image for a product (only one allowed per product)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'image_url' => 'required|file|mimes:jpeg,png,jpg,webp,gif',
        ]);

        // Check if size image already exists for this product
        $existingSizeImage = ProductSizeImage::where('product_id', $validated['product_id'])->first();
        
        if ($existingSizeImage) {
            return response()->json([
                'message' => 'Size image already exists for this product. Use update instead.',
                'size_image_id' => $existingSizeImage->id
            ], 400);
        }

        // Handle image upload
        if ($request->hasFile('image_url')) {
            $image = $request->file('image_url');
            $imageName = time() . '_' . str_replace(' ', '_', $image->getClientOriginalName());
            $imagePath = $image->storeAs('products/size-images', $imageName, 'public');
            $validated['image_url'] = $imagePath;
        }

        $sizeImage = ProductSizeImage::create($validated);
        
        // Return with full URL
        $sizeImageData = $sizeImage->load('product');
        $sizeImageData->image_url = asset('storage/' . $sizeImage->image_url);
        
        return response()->json($sizeImageData, 201);
    }

    /**
     * Update the size image for a product
     */
    public function update(Request $request, $productId)
    {
        $sizeImage = ProductSizeImage::where('product_id', $productId)->firstOrFail();

        $validated = $request->validate([
            'image_url' => 'required|file|mimes:jpeg,png,jpg,webp,gif',
        ]);

        // Handle image upload
        if ($request->hasFile('image_url')) {
            // Delete old image if exists
            if ($sizeImage->image_url && Storage::disk('public')->exists($sizeImage->image_url)) {
                Storage::disk('public')->delete($sizeImage->image_url);
            }
            
            $uploadedImage = $request->file('image_url');
            $imageName = time() . '_' . str_replace(' ', '_', $uploadedImage->getClientOriginalName());
            $imagePath = $uploadedImage->storeAs('products/size-images', $imageName, 'public');
            $validated['image_url'] = $imagePath;
        }

        $sizeImage->update($validated);
        
        // Return with full URL
        $sizeImageData = $sizeImage->load('product');
        if ($sizeImage->image_url) {
            $sizeImageData->image_url = asset('storage/' . $sizeImage->image_url);
        }
        
        return response()->json($sizeImageData);
    }

    /**
     * Remove the size image for a product
     */
    public function destroy($productId)
    {
        $sizeImage = ProductSizeImage::where('product_id', $productId)->firstOrFail();
        
        // Delete image file if exists
        if ($sizeImage->image_url && Storage::disk('public')->exists($sizeImage->image_url)) {
            Storage::disk('public')->delete($sizeImage->image_url);
        }
        
        $sizeImage->delete();
        return response()->json(['message' => 'Size image deleted successfully']);
    }
}