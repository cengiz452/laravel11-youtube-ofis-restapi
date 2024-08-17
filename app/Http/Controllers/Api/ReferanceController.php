<?php

namespace App\Http\Controllers\Api;

use App\Models\Referance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\UploadImageService;

class ReferanceController extends Controller
{
    public function index()
    {
        $referances = Referance::orderBy('id' , 'desc')->paginate(20);
        return response()->json($referances);
    }
    public function edit ($id)
    {
        $referance = Referance::where('id' , $id)->first();
        return response()->json($referance);
    }


    public function store(Request $request)
    {
        return $this->saveReferance($request, null);
    }

    public function update(Request $request, $id)
    {
        return $this->saveReferance($request, $id);
    }

    private function saveReferance(Request $request, $id = null)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
        ], [
            'name.required' => 'Referans Boş Geçilemez',
        ]);

        $referanceData = [
            'name' => $validatedData['name'],
            'link'=>$request->link ?? '#',
            'status' => $request->status ?? 1,
        ];

        $referance = $id ? Referance::find($id) : Referance::create($referanceData);

        if (!$referance) {
            return response()->json(['message' => 'Referans Bulunamadı'], 404);
        }

        if ($request->hasFile('file')) {
            $uploadedImages = $this->saveImageUpload($request, $referance);
            if (!empty($uploadedImages)) {
                $referance->image = $uploadedImages[0]['path'];
            }
        }

        $referance->update($referanceData);

        return response()->json([
            'message' => $id ? 'Başarıyla Referans Güncellendi.' : 'Başarıyla referans Oluşturuldu.',
            'data' => $referance
        ], $id ? 200 : 201);
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

        $uploadImageService->createFolder('uploads/referance');

        if (!empty($data->image)) {
            $uploadImageService->deleteFile($data->image);
        }

        // Eğer çoklu dosya yükleme gerekiyorsa, ilk dosyayı almak yeterli olabilir
        $uploadedImages = $uploadImageService->uploadMultipleImages($images);

        return $uploadedImages;
    }
}


