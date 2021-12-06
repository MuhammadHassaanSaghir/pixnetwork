<?php

namespace App\Library\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Throwable;


class Jwt_Token
{
  public function createToken($user_id)
  {
    try {
      date_default_timezone_set('Asia/Karachi');
      $issued_At = time() + 3600;
      $payload = array(
        "iss" => "http://127.0.0.1:8000",
        "aud" => "http://127.0.0.1:8000",
        "iat" => time(),
        "exp" => $issued_At,
        "data" => $user_id,
      );
      $jwt = JWT::encode($payload, config('JWT_Constant.secret_key'), 'HS256');
      return $jwt;
    } catch (Throwable $e) {
      return response(['message' => $e->getMessage()]);
    }
  }

  public function emailToken($data)
  {
    try {
      date_default_timezone_set('Asia/Karachi');
      $issued_At = time() + 3600;
      $payload = array(
        "iss" => "http://127.0.0.1:8000",
        "aud" => "http://127.0.0.1:8000",
        "iat" => time(),
        "exp" => $issued_At,
        "data" => $data,
      );
      $jwt = JWT::encode($payload, config('JWT_Constant.secret_key'), 'HS256');
      return $jwt;
    } catch (Throwable $e) {
      return response(['message' => $e->getMessage()]);
    }
  }

  public function getToken(Request $request)
  {
    try {
      $currToken = $request->bearerToken();
      $decode = JWT::decode($currToken, new Key(config('JWT_Constant.secret_key'), 'HS256'));
      return $decode->data;
    } catch (Throwable $e) {
      return response(['message' => $e->getMessage()]);
    }
  }
}
