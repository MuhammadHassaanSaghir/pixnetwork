<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImageRequest;
use App\Http\Resources\ImageResource;
use App\Library\Services\Jwt_Token;
use App\Models\Images;
use Illuminate\Http\Request;
use Throwable;

class ImagesController extends Controller
{
    public function upload(Jwt_Token $token, ImageRequest $request)
    {
        try {
            $request->validated();
            if ($request->privacy == 0 or $request->privacy == 1) {
                $file = $request->file('image')->store('upload_images');
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                $uploadImage = Images::create([
                    'user_id' => $token->getToken($request),
                    'image_name' => $request->name,
                    'image_path' => $file,
                    'extension' => $extension,
                    'privacy' => $request->privacy,
                ]);
                if (isset($uploadImage)) {
                    return response()->success('Image Upload Successfully', new ImageResource($uploadImage));
                } else {
                    return response()->error('Something Went Wrong');
                }
            } else {
                return response()->error('You have to required place 0 => (Public) / 1 => (Private) ');
            }
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage() . " Line No. " . $e->getLine()]);
        }
    }
}
