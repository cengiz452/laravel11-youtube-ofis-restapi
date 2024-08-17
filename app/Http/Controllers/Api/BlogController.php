<?php
namespace App\Http\Controllers\Api;

use App\Models\Blog;
use Illuminate\Http\Request;
use App\Http\Requests\BlogRequest;
use App\Http\Controllers\Controller;
use App\Services\UploadImageService;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::with('category')->orderBy('id', 'desc')->paginate(20);
        return response()->json($blogs);
    }
    public function edit ($id)
    {
        $blog = Blog::where('id' , $id)->with('category')->first();
        return response()->json($blog);
    }

    public function store(BlogRequest $request)
    {
        return $this->saveTag($request, null);
    }

    public function update(BlogRequest $request, $id)
    {
        return $this->saveTag($request, $id);
    }

    private function saveTag($request, $id = null)
    {
        /*$validatedData = $request->validate([
            'name' => 'required|string',
        ], [
            'name.required' => 'Kategori Boş Geçilemez',
        ]);*/

        $blogData = [
            'name' => $request->name,
            'content' => $request->input('content'),
            'category_id' => $request->input('category_id'),
            'status' => $request->input('status', 1),
        ];

        $blog = $id ? Blog::find($id) : Blog::create($blogData);

        if (!$blog) {
            return response()->json(['message' => 'Blog bulunamadı.'], 404);
        }

        if ($request->hasFile('file')) {
            $uploadedImages = $this->saveImageUpload($request, $blog);
            if (!empty($uploadedImages)) {
                $blog->image = $uploadedImages[0]['path'];
            }
        }

        $blog->slug = null; // If slug processing will be done here, adjust it
        $blog->update($blogData);

        return response()->json([
            'message' => $id ? 'Blog başarıyla güncellendi.' : 'Blog başarıyla oluşturuldu.',
            'data' => $blog
        ], $id ? 200 : 201); // Update status code accordingly
    }

    private function saveImageUpload(Request $request, Blog $blog)
    {
        $images = $request->file('file');

        if (!$images) {
            return [];
        }

        $uploadImageService = new UploadImageService();
        $uploadImageService->createFolder('uploads/blogs');

        if (!empty($blog->image)) {
            $uploadImageService->deleteFile($blog->image);
        }

        // Upload multiple images
        $uploadedImages = $uploadImageService->uploadMultipleImages($images);

        return $uploadedImages;
    }
}
