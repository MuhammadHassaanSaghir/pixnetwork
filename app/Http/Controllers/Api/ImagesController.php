<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImageRequest;
use App\Http\Requests\PrivacyRequest;
use App\Http\Requests\SearchImageRequest;
use App\Http\Requests\ShareLinkRequest;
use App\Http\Resources\ImageResource;
use App\Http\Resources\ShareLinkResource;
use App\Models\Images;
use App\Models\Sharelink;
use App\Models\Token;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ImagesController extends Controller
{
    public function upload(ImageRequest $request)
    {
        try {
            $request->validated();
            $file = $request->file('image')->store('public/upload_images');
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            $uploadImage = Images::create([
                'user_id' => $request->user_id,
                'image_name' => $request->name,
                'image_path' => $file,
                'extension' => $extension,
            ]);
            if (isset($uploadImage)) {
                return response()->success('Image Upload Successfully', new ImageResource($uploadImage));
            } else {
                return response()->error('Something Went Wrong');
            }
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage() . " Line No. " . $e->getLine()]);
        }
    }
    public function remove(Request $request, $id)
    {
        try {
            $images = Images::where('id', $id)->where('user_id', $request->user_id)->first();
            if (json_decode($images)) {
                if ($images->image_path != null) {
                    unlink(storage_path('app/' . $images->image_path));
                }
                $images->delete();
                return response([
                    'message' => 'Image has been Deleted',
                ]);
            } else {
                return response()->error('You Unauthorize to Delete Image');
            }
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage() . " Line No. " . $e->getLine()]);
        }
    }

    public function fetch(Request $request)
    {
        try {
            $fetchImage = Images::where('user_id', $request->user_id)->get();
            if (json_decode($fetchImage)) {
                return ImageResource::collection($fetchImage);
            } else {
                return response()->error('No Image Found');
            }
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage() . " Line No. " . $e->getLine()]);
        }
    }

    public function changePrivacy(PrivacyRequest $request, $id)
    {
        try {
            $request->validated();
            $fetchImage = Images::where('id', $id)->where('user_id', $request->user_id)->first();
            if (json_decode($fetchImage)) {
                if ($request->privacy == 0 or $request->privacy == 1 or $request->privacy == 2) {
                    $fetchImage->privacy = $request->privacy;
                    $fetchImage->save();
                    return response()->success('Privacy Updated Successfully', new ImageResource($fetchImage));
                } else {
                    return response()->error('You have required to place 0 => (Hidden) / 1 => (Public) / 2 => (Private)');
                }
            } else {
                return response()->error('No Image Found');
            }
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage() . " Line No. " . $e->getLine()]);
        }
    }

    public function searchImage(SearchImageRequest $request)
    {
        try {
            $request->validated();
            $images = Images::where(DB::raw('CONCAT_WS(" ", image_name, extension, privacy)'), 'like', '%' . $request->search . '%')->orwhereDate('created_at', $request->search)->orwhereTime('created_at', '=', $request->search)->where('user_id', $request->user_id)->get();
            if (json_decode($images)) {
                return response()->success('Image Found Successfully', ImageResource::collection($images));
            } else {
                return response()->error('No Image Found');
            }
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage() . " Line No. " . $e->getLine()]);
        }
    }

    public function shareLink(ShareLinkRequest $request, $id)
    {
        try {
            $request->validated();
            $visibility = null;
            $email = null;
            if ($request->visibility == 1 and $request->email != null) {
                $visibility = 1;
                $userExist = User::where('email', $request->email)->where('email_verified_at', '!=', null)->first();
                if (empty($userExist)) {
                    return response()->error('Email cannot registered or not verified');
                } else {
                    $email = $userExist->email;
                }
            } else {
                return response()->error('You have required to place 1 => (Private)');
            }
            $random = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 10);
            $link = url('api/image/view/' . $random);
            $shareLink = Sharelink::create([
                'user_id' => $request->user_id,
                'image_id' => $id,
                'link' => $random,
                'visibility' => $visibility,
                'email' => $email,
            ]);
            if (isset($shareLink)) {
                return response()->success('Link Generate Successfully', new ShareLinkResource($shareLink, $link));
            } else {
                return response()->error('Something Went Wrong');
            }
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage() . " Line No. " . $e->getLine()]);
        }
    }

    public function view(Request $request, $id)
    {
        try {
            $link = Sharelink::where('link', $id)->first();
            if ($link->visibility === null) {
                $image = Images::find($link->image_id);
                if ($image->privacy != 2) {
                    return view('image', ['image' => $image->image_path]);
                } else {
                    return response()->error('Image is Private');
                }
            } else {
                $loggedIn = Token::where('user_id', $request->user_id)->orwhere('user_id', $link->user_id)->first();
                if (empty($loggedIn)) {
                    return response()->error('You have required to Login as ' . $link->email);
                } else {
                    $image = Images::find($link->image_id);
                    return view('image', ['image' => $image->image_path]);
                }
            }
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage() . " Line No. " . $e->getLine()]);
        }
    }
}
