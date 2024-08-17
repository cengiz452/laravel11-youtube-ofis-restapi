<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Career;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\UploadImageService;

class CareerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $careers = Career::all();
        return response()->json($careers);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        return $this->saveCareer($request);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        return $this->saveCareer($request, $id);
    }

    private function saveCareer(Request $request, $id = null)
    {
        $validatedData = $request->validate([
            'title' => 'required|string',
            'company' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'description' => 'nullable|string',
        ], [
            'title.required' => 'Başlık boş geçilemez',
            'company.required' => 'Şirket adı boş geçilemez',
        ]);

        $startDate = Carbon::parse($validatedData['start_date'])->format('Y-m-d');
        $endDate = isset($validatedData['end_date']) ? Carbon::parse($validatedData['end_date'])->format('Y-m-d') : null;

        $careerData = [
            'title' => $validatedData['title'],
            'company' => $validatedData['company'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'description' => $validatedData['description'],
            'status' => $request->status ?? (empty($endDate) ? 0 : 1),
        ];

        $career = $id ? Career::find($id) : null;

        if ($career) {
            $career->update($careerData);
        } else {
            $career = Career::create($careerData);
        }

        if ($request->hasFile('file')) {
            $uploadedImages = $this->saveImageUpload($request, $career);

            if (!empty($uploadedImages) && isset($uploadedImages[0]['path'])) {
                $career->image = $uploadedImages[0]['path'];
                $career->save();
            }
        }

        return response()->json([
            'message' => $id ? 'Başarıyla kariyer güncellendi.' : 'Başarıyla kariyer oluşturuldu.',
            'data' => $career,
        ]);
    }

    private function saveImageUpload(Request $request, $career)
    {
        $images = $request->file('file');
        if (!$images) {
            return [];
        }

        // Eğer tek bir dosya varsa, bunu bir diziye dönüştür
        if (!is_array($images)) {
            $images = [$images];
        }

        $uploadImageService = new UploadImageService();
        $uploadImageService->createFolder('uploads/kariyer');

        // Eğer önceki bir resim varsa, sil
        if (!empty($career->image)) {
            $uploadImageService->deleteFile($career->image);
        }

        // Resimleri yükle
        $uploadedImages = $uploadImageService->uploadMultipleImages($images);

        return $uploadedImages;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Career  $career
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Career $career)
    {
        // Resim dosyasını sil
        if (!empty($career->image)) {
            $uploadImageService = new UploadImageService();
            $uploadImageService->deleteFile($career->image);
        }

        $career->delete();
        return response()->json(null, 204);
    }
}
