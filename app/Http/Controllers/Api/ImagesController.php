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
            $uploadImage = time() . "-" . $request->file('image')->getClientOriginalName();
            $request->file('image')->move(public_path('upload_images/'), $uploadImage);
            $extension = pathinfo($uploadImage, PATHINFO_EXTENSION);
            $random = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 10); // GENERATE RANDOM CODE TO ACCESS IMAGE
            $uploadImage = Images::create([
                'user_id' => $request->user_id,
                'image_name' => $request->name,
                'image_path' => $uploadImage,
                'extension' => $extension,
                'link' => $random,
            ]);
            if (isset($uploadImage)) {
                return response()->success('Image Upload Successfully', new ImageResource($uploadImage), 200);
            } else {
                return response()->error('Something Went Wrong', 201);
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
                return response()->success('Image Deleted Successfully', 200);
            } else {
                return response()->error('You Unauthorize to Delete Image', 401);
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
                return response()->error('No Image Found', 204);
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
                    return response()->success('Privacy Updated Successfully', new ImageResource($fetchImage), 200);
                } else {
                    return response()->error('You have required to place 0 => (Hidden) / 1 => (Public) / 2 => (Private)', 400);
                }
            } else {
                return response()->error('No Image Found', 204);
            }
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage() . " Line No. " . $e->getLine()]);
        }
    }

    public function searchImage(SearchImageRequest $request)
    {
        try {
            $request->validated();
            // $images = Images::where(DB::raw('CONCAT_WS(" ", image_name, extension, privacy)'), 'like', '%' . $request->search . '%')->orwhereDate('created_at', $request->search)->orwhereTime('created_at', '=', $request->search)->where('user_id', $request->user_id)->get();
            $images = Images::orWhere('image_name', 'like', '%' . $request->search . '%')->orWhere('extension', 'like', '%' . $request->search . '%')->orWhere('privacy', $request->search)->where('user_id', $request->user_id)->get();
            if (json_decode($images)) {
                return response()->success('Image Found Successfully', ImageResource::collection($images), 400);
            } else {
                return response()->error('No Image Found', 204);
            }
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage() . " Line No. " . $e->getLine()]);
        }
    }

    public function shareLink(Request $request, $id)
    {
        try {
            if ($request->sender_id != null) {
                $break = explode(",", $request->sender_id); //BREAK ARRAY TO GET USERS
                foreach ($break as $value) {
                    $sender_id = trim($value);
                    $userExist = User::where('id', $sender_id)->where('email_verified_at', '!=', null)->first();
                    if (empty($userExist)) {
                        return response()->error($sender_id . ' user cannot registered or not verified', 401);
                    }
                    $linkExist = Sharelink::where('image_id', $id)->where('sender_id', null)->first();
                    if (!empty($linkExist)) {
                        $linkExist->delete();
                    }
                    $image = Images::find($id);
                    $link = url('api/image/view/' . $image->link);
                    $linkImage = Sharelink::updateOrCreate([
                        'sender_id' => $sender_id, 'image_id' => $id
                    ], [
                        'user_id' => $request->user_id,
                        'image_id' => $id,
                        'link' => $link,
                        'sender_id' => $sender_id,
                    ]);
                }
                if (isset($linkImage)) {
                    return response()->success('Link Generate Successfully', new ShareLinkResource($break, $link), 200);
                } else {
                    return response()->error('Something Went Wrong', 201);
                }
            } else {
                $linkExist = Sharelink::where('image_id', $id)->where('user_id', $request->user_id);
                if (!empty($linkExist)) {
                    $linkExist->delete();
                }
                $image = Images::find($id);
                $link = url('api/image/view/' . $image->link);
                $linkImage = Sharelink::create([
                    'user_id' => $request->user_id,
                    'image_id' => $id,
                    'link' => $link,
                    'sender_id' => null,
                ]);
                if (isset($linkImage)) {
                    return response()->success('Link Generate Successfully', new ShareLinkResource(null, $link), 200);
                } else {
                    return response()->error('Something Went Wrong', 201);
                }
            }
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage() . " Line No. " . $e->getLine()]);
        }
    }

    public function removeAccess(ShareLinkRequest $request, $id)
    {
        try {
            $break = explode(",", $request->sender_id); //BREAK ARRAY TO GET USERS
            foreach ($break as $value) {
                $sender_id = trim($value);
                $userExist = Sharelink::where('sender_id', $sender_id)->where('image_id', $id)->first();
                if (empty($userExist)) {
                    return response()->error($sender_id . ' user cannot exists', 401);
                } else {
                    $removeAccess = $userExist->delete();
                }
            }
            if (isset($removeAccess)) {
                return response()->success('User Access has been Removed Successfully');
            } else {
                return response()->error('Something Went Wrong', 201);
            }
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage() . " Line No. " . $e->getLine()]);
        }
    }

    public function view(Request $request, $id)
    {
        try {
            $link = Images::where('link', $id)->first();
            if (!empty($link)) {
                if ($link->privacy == 0) {
                    return response()->error('Image is Hidden', 401);
                } elseif ($link->privacy == 1) {
                    return view('image', ['image' => $link->image_path]);
                } elseif ($link->privacy == 2) {
                    $viewer = Sharelink::where('sender_id', $request->user_id)->orWhere('user_id', $request->user_id)->first();
                    if (!empty($viewer)) {
                        return view('image', ['image' => $link->image_path]);
                    } else {
                        return response()->error('Image is Private', 401);
                    }
                }
            } else {
                return response()->error('No Image Found', 400);
            }
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage() . " Line No. " . $e->getLine()]);
        }
    }
}
