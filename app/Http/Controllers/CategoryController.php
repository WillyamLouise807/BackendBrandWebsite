<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index()
    {
        $categories = Category::withCount('products')->get();
        
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
        if($request->hasFile('image_url')) {
            $uploadedFile = Cloudinary::upload($request->file('image_url')->getRealPath(), [
                'folder' => 'uploads/category',
            ]);

            // Ambil URL aman dan public_id untuk file yang diunggah
            $validated['image_url'] = $uploadedFile->getSecurePath(); // URL aman
        }

        $category = Category::create($validated);
        
        return response()->json($category, 201);
    }

    /**
     * Display the specified category
     */
    public function show($id)
    {
        // 
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

        if ($request->hasFile('image_url')) {
            if ($category->image_url) {
                // Ekstrak public_id dari URL
                $fileUrl = $category->image_url;
                $publicId = substr(
                    $fileUrl,
                    strpos($fileUrl, 'uploads/category/'),
                    strrpos($fileUrl, '.') - strpos($fileUrl, 'uploads/category/')
                );

                // return($publicId);

                // Hapus file lama di Cloudinary
                Cloudinary::destroy($publicId);
            }

            // Upload gambar baru ke Cloudinary
            $uploadedFile = Cloudinary::upload($request->file('image_url')->getRealPath(), [
                'folder' => 'uploads/category',
            ]);
            $validated['image_url'] = $uploadedFile->getSecurePath(); // URL file baru
        } else {
            // Jika tidak ada file baru, tetap gunakan image_url lama
            $validated['image_url'] = $category->image_url;
        }

        $category->update($validated);
        
        return response()->json($category);
    }

    /**
     * Remove the specified category
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        $fileUrl = $category->image_url;

        if($category){
            // Hapus gambar lama
            $publicId = substr($fileUrl, strpos($fileUrl, 'uploads/category/'), strrpos($fileUrl, '.') - strpos($fileUrl, 'uploads/category/'));

            // Hapus file lama di Cloudinary
            Cloudinary::destroy($publicId);

            $category->delete();
            $data["success"] = true;
            $data["message"] = "Category deleted successfully";
            return response()->json($data, Response::HTTP_OK);
        }else {
            $data["success"] = false;
            $data["message"] = "Category not found";
            return response()->json($data, Response::HTTP_NOT_FOUND);
        }
    }
}