<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class VerifyEmail
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = User::where('email', $request->email)->first();
        if (empty($user)) {
            return response()->json([
                'message' => 'Please Register First',
            ]);
        } elseif ($user->email_verified_at == null) {
            return response()->json([
                'message' => 'Please Confirm Your Email',
            ]);
        } else {
            return $next($request);
        }
    }
}
