<?php

namespace App\Http\Controllers\Api;


use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\UploadImageService;

class CategoryController extends Controller
{

    public function index()
    {
        $categories = Category::paginate(20);
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        return $this->saveCategory($request);
    }

    public function update(Request $request, $id)
    {
        return $this->saveCategory($request, $id);
    }

    private function saveCategory(Request $request, $id = null)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
        ], [
            'name.required' => 'Kategori Boş Geçilemez',
        ]);

        $categoryData = [
            'name' => $validatedData['name'],
            'status' => $request->status ?? 1,
        ];

        $category = $id ? Category::find($id) : Category::create($categoryData);

        if (!$category) {
            return response()->json(['message' => 'Kategori Bulunamadı'], 404);
        }

        if ($request->hasFile('file')) {
            $uploadedImages = $this->saveImageUpload($request, $category);
            if (!empty($uploadedImages)) {
                $category->image = $uploadedImages[0]['path'];
            }
        }
        $category->slug = null;
        $category->update($categoryData);

        return response()->json([
            'message' => $id ? 'Başarıyla Kategori Güncellendi.' : 'Başarıyla Kategori Oluşturuldu.',
            'data' => $category
        ], 200);
    }

    private function saveImageUpload(Request $request, $data)
    {
        $images = $request->file('file');

        if (!$images) {
            return []; // Eğer dosya yoksa boş döner
        }
        if(!is_array($images)){
            $images = [$images];
        } else{
            $images = [$images];
        }

        $uploadImageService = new UploadImageService();

        $uploadImageService->createFolder('uploads/category');

        if (!empty($data->image)) {
            $uploadImageService->deleteFile($data->image);
        }

        // Eğer çoklu dosya yükleme gerekiyorsa, ilk dosyayı almak yeterli olabilir
        $uploadedImages = $uploadImageService->uploadMultipleImages($images);

        return $uploadedImages;
    }
}


