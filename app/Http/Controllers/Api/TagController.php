<?php

namespace App\Http\Controllers\Api;

use App\Models\Tag;
use Illuminate\Http\Request;
use App\Http\Requests\BlogRequest;
use App\Http\Controllers\Controller;
use App\Services\UploadImageService;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::paginate(20);
        return response()->json($tags);
    }
    public function edit ($id)
    {
        $tag = Tag::where('id' , $id)->first();
        return response()->json($tag);
    }


    public function store(BlogRequest $request)
    {
        return $this->saveTag($request);
    }

    public function update(BlogRequest $request, $id)
    {
        return $this->saveTag($request, $id);
    }

    private function saveTag(Request $request, $id = null)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
        ], [
            'name.required' => 'Etiket Boş Geçilemez',
        ]);

        $tagData = [
            'name' => $validatedData['name'],
            'status' => $request->status ?? 1,
        ];

        $tag = $id ? Tag::find($id) : Tag::create($tagData);

        if (!$tag) {
            return response()->json(['message' => 'Etiket Bulunamadı'], 404);
        }

        if ($request->hasFile('file')) {
            $uploadedImages = $this->saveImageUpload($request, $tag);
            if (!empty($uploadedImages)) {
                $tag->image = $uploadedImages[0]['path'];
            }
        }
        $tag->slug = null;
        $tag->update($tagData);

        return response()->json([
            'message' => $id ? 'Başarıyla Etiket Güncellendi.' : 'Başarıyla Etiket Oluşturuldu.',
            'data' => $tag
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

        $uploadImageService->createFolder('uploads/tag');

        if (!empty($data->image)) {
            $uploadImageService->deleteFile($data->image);
        }

        // Eğer çoklu dosya yükleme gerekiyorsa, ilk dosyayı almak yeterli olabilir
        $uploadedImages = $uploadImageService->uploadMultipleImages($images);

        return $uploadedImages;
    }
}
