<?php

namespace App\Http\Controllers\Api;

use App\Models\About;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\UploadImageService;

class AboutController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function index()
    {
        $about = About::firstOrFail();
        return response()->json($about);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
        ]);

        $about = About::firstOrFail(); // Ensure we're updating the first (or only) record

        if ($request->hasFile('file')) {
            $image = $request->file('file');

            $uploadImageService = new UploadImageService();
            $uploadedImages = $uploadImageService->uploadMultipleImages([$image]);

            $request->merge(['image' => $uploadedImages[0]['path']]);
        }

        $about->update($request->only('title', 'image', 'content'));

        return response()->json(['message' => 'Başarıyla Güncellendi.', 'data' => $about], 200);
    }
}
