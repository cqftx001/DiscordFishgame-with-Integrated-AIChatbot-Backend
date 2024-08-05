<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthMiddleware
{
    /**
     * 鉴权
     * @param Request $request
     * @param Closure $next
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function handle(Request $request, Closure $next)
    {
        Log::debug('header:',$request->header());
        Log::debug('request:',$request->all());
        $token = $request->header('token');
        $timestamp = $request->header('timestamp');
        $password = config('myauth.password');
        $correctToken = md5($password.$timestamp.$password);
        //todo  上线把时间限制改为 60s
//        if((time() - $timestamp > 6000000)|| (time() - $timestamp < 0) || $token != $correctToken){
//            return response()->json(['code' => 401,'msg' => 'token invalid','data' => new \stdClass()], 401);
//        }
        return $next($request);
    }
}
