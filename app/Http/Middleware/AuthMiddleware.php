<?php

namespace App\Http\Middleware;

use App\Library\Services\Jwt_Token;
use App\Models\Token;
use Closure;
use Illuminate\Http\Request;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    protected $authUser;

    public function __construct(Jwt_Token $token, Request $request)
    {
        $this->authUser = $token->getToken($request);
    }

    public function handle(Request $request, Closure $next)
    {
        $currToken = $request->bearerToken();
        if (empty($currToken)) {
            return response([
                'message' => 'Please Enter Token',
            ]);
        } else {
            $request = $request->merge(array('user_id' => $this->authUser));
            $userExist = Token::where('user_id', $request->user_id)->first();
            if (!isset($userExist)) {
                return response([
                    'message' => 'Unauthenticated',
                ]);
            } else {
                return $next($request);
            }
        }
    }
}
