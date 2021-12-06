<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Jobs\EmailJob;
use Illuminate\Http\Request;
use App\Library\Services\Jwt_Token;
use App\Models\Token;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Throwable;

class UserController extends Controller
{

    public function register(Jwt_Token $token, RegisterRequest $request)
    {
        try {
            $request->validated();
            $emailToken = $token->emailToken(time());
            $url = url('api/user/EmailConfirmation/' . $request->email . '/' . $emailToken);
            EmailJob::dispatch($request->email, $url, $request->name)->delay(now()->addSeconds(10));

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'age' => $request->age,
                'image' => $request->file('image')->store('user_images'),
                'email_token' => $emailToken,
            ]);
            if (isset($user)) {
                return response()->json([
                    'message' => 'Verification Link has been Sent. Check Your Mail',
                ]);
            } else {
                return response()->json([
                    'message' => 'Something Went Wrong While Sending Email',
                ]);
            }
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage() . " Line No. " . $e->getLine()]);
        }
    }

    public function verify($email, $hash)
    {
        try {
            $userExist = User::where('email', $email)->first();
            if (!isset($userExist)) {
                return response()->json([
                    'message' => 'Something went wrong',
                ]);
            } elseif ($userExist->email_verified_at != null) {
                return response()->json([
                    'message' => 'Link has been Expired',
                ]);
            } elseif ($userExist->email_token != $hash) {
                return response()->json([
                    'message' => 'Unauthenticated',
                ]);
            } else {
                $userExist->email_verified_at = time();
                $userExist->save();
                return response()->json([
                    'message' => 'Now your pixNetwork Account has been Verified',
                ]);
            }
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage() . " Line No. " . $e->getLine()]);
        }
    }

    public function login(Jwt_Token $token, LoginRequest $request)
    {
        try {
            $request->validated();
            $user = User::where('email', $request->email)->first();

            if (($request->email != $user->email) or (!Hash::check($request->password, $user->password))) {
                return response()->json([
                    'message' => 'Incorrect Credentials',
                    'status' => '401',
                ]);
            } elseif ($user->email_verified_at == null) {
                return response()->json([
                    'message' => 'Please Confirm Your Email',
                ]);
            } else {
            }

            $token = $token->createToken($user->id);
            $alreadyExist = Token::where('user_id', $user->id)->first();
            if (isset($alreadyExist)) {
                $alreadyExist->update([
                    'expired_at' => date("Y-m-d H:i:s", strtotime('+1 hours')),
                    'token' => $token,
                ]);
                return response()->json([
                    'data' => new UserResource($user),
                    'token' => $token,
                ]);
            } else {
                Token::create([
                    'user_id' => $user->id,
                    'expired_at' => date("Y-m-d H:i:s", strtotime('+1 hours')),
                    'token' => $token,
                ]);

                return response()->json([
                    'data' => new UserResource($user),
                    'token' => $token,
                ]);
            }
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage() . " Line No. " . $e->getLine()]);
        }
    }

    public function update(UpdateUserRequest $request)
    {
        try {
            $request->validated();
            $user = User::find($request->user_id);
            if (isset($user)) {
                if (isset($request->name)) {
                    $user->name = $request->name;
                    $user->save();
                }
                if (isset($request->age)) {
                    $user->age = $request->age;
                    $user->save();
                }
                if (isset($request->image)) {
                    unlink(storage_path('app/' . $user->image));
                    $user->image = $request->file('image')->store('user_images');
                    $user->save();
                }
                return response()->json([
                    'message' => 'Profile Updated',
                ]);
            } else {
                return response()->json([
                    'message' => 'No User Found',
                ]);
            }
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage() . " Line No. " . $e->getLine()]);
        }
    }

    public function update_password(UpdatePasswordRequest $request)
    {
        try {
            $request->validated();
            $user = User::find($request->user_id);
            $check_pass = Hash::check($request->current_password, $user->password);
            if (($user and $check_pass) == true) {
                $password_update = $user->update(['password' => Hash::make($request->new_password)]);
                if (isset($password_update)) {
                    return response()->json([
                        'message' => 'Password Updated Successfully',
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Something Went Wrong',
                    ]);
                }
            } else {
                return response()->json([
                    'message' => 'Your Current Password is Wrong',
                ]);
            }
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage() . " Line No. " . $e->getLine()]);
        }
    }

    public function logout(Request $request)
    {
        try {
            $token_delete = Token::where('user_id', $request->user_id)->first();
            if ($token_delete->delete()) {
                return response()->json([
                    'message' => 'Logout Successfully',
                ]);
            }
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage() . " Line No. " . $e->getLine()]);
        }
    }
}
