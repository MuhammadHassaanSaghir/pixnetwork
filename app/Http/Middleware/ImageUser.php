<?php

namespace App\Http\Middleware;

use App\Models\Images;
use Closure;
use Illuminate\Http\Request;

class ImageUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle(Request $request, Closure $next, $id)
    {
        dd($id);
        $checkUser = Images::where('user_id', $request->user_id)->get();
        if (empty($checkUser)) {
            return response()->error('Unauthenticated');
        } else {
            return $next($request);
        }
        // return $next($request);
    }
}
