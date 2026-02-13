<?php

namespace App\Http\Controllers;

use App\Models\ProductSizeImage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

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
        if($request->hasFile('image_url')) {
            $uploadedFile = Cloudinary::upload($request->file('image_url')->getRealPath(), [
                'folder' => 'uploads/product-size-image',
            ]);

            // Ambil URL aman dan public_id untuk file yang diunggah
            $validated['image_url'] = $uploadedFile->getSecurePath(); // URL aman
        }

        $sizeImage = ProductSizeImage::create($validated);
        
        return response()->json($sizeImage, 201);
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
            if ($sizeImage->image_url) {
                // Ekstrak public_id dari URL
                $fileUrl = $sizeImage->image_url;
                $publicId = substr(
                    $fileUrl,
                    strpos($fileUrl, 'uploads/product-size-image/'),
                    strrpos($fileUrl, '.') - strpos($fileUrl, 'uploads/product-size-image/')
                );

                // return($publicId);

                // Hapus file lama di Cloudinary
                Cloudinary::destroy($publicId);
            }

            // Upload gambar baru ke Cloudinary
            $uploadedFile = Cloudinary::upload($request->file('image_url')->getRealPath(), [
                'folder' => 'uploads/product-size-image',
            ]);
            $validated['image_url'] = $uploadedFile->getSecurePath(); // URL file baru
        } else {
            // Jika tidak ada file baru, tetap gunakan image_url lama
            $validated['image_url'] = $sizeImage->image_url;
        }

        $sizeImage->update($validated);
        
        return response()->json($sizeImage);
    }

    /**
     * Remove the size image for a product
     */
    public function destroy($productId)
    {
        $sizeImage = ProductSizeImage::where('product_id', $productId)->firstOrFail();
        
        $fileUrl = $sizeImage->image_url;

        if($sizeImage){
            // Hapus gambar lama
            $publicId = substr($fileUrl, strpos($fileUrl, 'uploads/product-size-image/'), strrpos($fileUrl, '.') - strpos($fileUrl, 'uploads/product-size-image/'));

            // Hapus file lama di Cloudinary
            Cloudinary::destroy($publicId);

            $sizeImage->delete();
            $data["success"] = true;
            $data["message"] = "Size Image deleted successfully";
            return response()->json($data, Response::HTTP_OK);
        }else {
            $data["success"] = false;
            $data["message"] = "Size Image not found";
            return response()->json($data, Response::HTTP_NOT_FOUND);
        }
    }
}