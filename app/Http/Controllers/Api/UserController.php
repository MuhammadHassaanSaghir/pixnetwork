<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Jobs\EmailJob;
use App\Jobs\ResetPasswordJob;
use Illuminate\Http\Request;
use App\Library\Services\Jwt_Token;
use App\Models\password_reset;
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
            $profileImage = time() . "-" . $request->file('image')->getClientOriginalName();
            $request->file('image')->move(public_path('user_images/'), $profileImage);
            $url = url('api/user/emailConfirmation/' . $request->email . '/' . $emailToken);
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'age' => $request->age,
                // 'image' => 0,
                'image' => $profileImage,
                'email_token' => $emailToken,
            ]);
            if (isset($user)) {
                EmailJob::dispatch($request->email, $url, $request->name)->delay(now()->addSeconds(10));
                return response()->success('Verification Link has been Sent. Check Your Mail');
            } else {
                return response()->error('Something Went Wrong While Sending Email', 201);
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
                return response()->error('Something went wrong', 201);
            } elseif ($userExist->email_verified_at != null) {
                return response()->error('Link has been Expired', 401);
            } elseif ($userExist->email_token != $hash) {
                return response()->error('Unauthenticated', 401);
            } else {
                $userExist->email_verified_at = time();
                $userExist->save();
                return response()->success('Now your pixNetwork Account has been Verified');
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
                return response()->error('Incorrect Credentials', 401);
            }
            $token = $token->createToken($user->id);
            $alreadyExist = Token::where('user_id', $user->id)->first();
            if (isset($alreadyExist)) {
                $alreadyExist->update([
                    'expired_at' => date("Y-m-d H:i:s", strtotime('+1 hours')),
                    'token' => $token,
                ]);
                $data = [
                    'User' => new UserResource($user),
                    'Token' => $token,
                ];
                return response()->success('Successfully Login', $data, 200);
            } else {
                Token::create([
                    'user_id' => $user->id,
                    'expired_at' => date("Y-m-d H:i:s", strtotime('+1 hours')),
                    'token' => $token,
                ]);
                $data = [
                    'User' => new UserResource($user),
                    'Token' => $token,
                ];
                return response()->success('Successfully Login', $data, 200);
            }
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage() . " Line No. " . $e->getLine()]);
        }
    }

    public function forgotPassword(Jwt_Token $token, ForgotPasswordRequest $request)
    {
        try {
            $request->validated();
            $resetToken = $token->emailToken(time());
            $url = url('api/user/resetPassword/' . $request->email . '/' . $resetToken);
            $user = User::where('email', $request->email)->first();
            if (isset($user)) {
                $tokenExist = password_reset::where('email', $request->email)->where('expire', '0')->first();
                if (isset($tokenExist)) {
                    $tokenExist->delete();
                }
                password_reset::create([
                    'email' => $request->email,
                    'token' => $resetToken,
                ]);
                ResetPasswordJob::dispatch($request->email, $url)->delay(now()->addSeconds(10));
                return response()->success('Reset Link has been Sent. Check you Mail', 200);
            } else {
                return response()->error('Something went wrong', 201);
            }
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage() . " Line No. " . $e->getLine()]);
        }
    }

    public function resetPassword($email, $hash, ResetPasswordRequest $request)
    {
        try {
            $request->validated();
            $tokenExist = password_reset::where('token', $hash)->where('expire', '0')->first();
            if (!isset($tokenExist)) {
                return response()->error('Link has been Expired');
            } else {
                $user = User::where('email', $email)->first();
                $password_update = $user->update(['password' => Hash::make($request->new_password)]);
                $tokenExist->where('token', $hash)->update(['expire' => '1']);
                if (isset($password_update)) {
                    return response()->success('Password Updated Successfully', 200);
                } else {
                    return response()->error('Something Went Wrong', 201);
                }
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
                    unlink(public_path('user_images/'));
                    $profileImage = time() . "-" . $request->file('image')->getClientOriginalName();
                    $request->file('image')->move(public_path('user_images/'), $profileImage);
                    $user->image = $profileImage;
                    $user->save();
                }
                return response()->success('Profile Updated', new UserResource($user), 200);
            } else {
                return response()->error('Something Went Wrong', 201);
            }
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage() . " Line No. " . $e->getLine()]);
        }
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        try {
            $request->validated();
            $user = User::find($request->user_id);
            $check_pass = Hash::check($request->current_password, $user->password);
            if (($user and $check_pass) == true) {
                $password_update = $user->update(['password' => Hash::make($request->new_password)]);
                if (isset($password_update)) {
                    return response()->success('Password Updated Successfully', 200);
                } else {
                    return response()->error('Something Went Wrong', 201);
                }
            } else {
                return response()->error('Your Current Password is Wrong', 401);
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
                return response()->success('Logout Successfully', 200);
            }
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage() . " Line No. " . $e->getLine()]);
        }
    }
}
