<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Validator;

class UploadImageService
{
    public function uploadMultipleImages($images, $folder = 'uploads')
    {
        // Ensure $images is always an array
        if (!$images instanceof \Illuminate\Http\UploadedFile) {
            $images = (array) $images;
        }

        // Validation rules
        $rules = [
            'image' => 'required|image|mimes:jpg,jpeg,png,gif,bmp,tiff,webp|max:2048',
        ];

        // Validate the images
        foreach ($images as $image) {
            $validator = Validator::make(['image' => $image], $rules);
            if ($validator->fails()) {
                throw new \InvalidArgumentException('Invalid image: ' . $validator->errors()->first());
            }
        }

        // Ensure directories exist
        $this->createFolder($folder);
        $this->createFolder('resized');

        // Sanitize the file names and upload images
        $sanitizedImages = [];
        foreach ($images as $image) {
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $image->getClientOriginalExtension();
            $newFilename = sprintf('%s-%s.%s', md5($originalName . microtime()), time(), $extension);

            // Store original image
            Storage::put($folder . '/' . $newFilename, file_get_contents($image));
            $sanitizedImages[] = $newFilename;
        }

        // Resize and store images
        foreach ($sanitizedImages as $filename) {
            $path = $folder . '/' . $filename;
            $resizedImage = Image::make(Storage::path($path))
                ->resize(500, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->encode('webp', 75);

            Storage::put('resized/' . $filename . '.webp', $resizedImage);
        }

        return array_map(function ($filename) use ($folder) {
            return [
                'path' => $folder . '/' . $filename,
                'resized_path' => 'resized/' . $filename . '.webp'
            ];
        }, $sanitizedImages);
    }


    public function deleteFile(string $filePath)
    {
        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
        }
    }

    public function createFolder(string $directoryPath)
    {
        if (!Storage::exists($directoryPath)) {
            Storage::makeDirectory($directoryPath);
        }
    }
}
