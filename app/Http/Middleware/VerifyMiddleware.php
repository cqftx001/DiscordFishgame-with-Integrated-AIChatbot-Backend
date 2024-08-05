<?php

namespace App\Http\Middleware;

use App\Models\Users;
use Closure;
use Illuminate\Http\Request;

class VerifyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $userId = $request->input('user_id');
        $count = Users::query()
            ->where('user_id',$userId)
            ->count();
        if(!$count){
            return response()->json(['code' => 404,'msg' => 'user not found','data' => new \stdClass()], 404);
        }
        return $next($request);
    }
}
