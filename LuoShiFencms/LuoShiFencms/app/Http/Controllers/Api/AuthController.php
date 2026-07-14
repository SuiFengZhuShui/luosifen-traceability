<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('account', 'password');

        if (Auth::guard('web')->attempt($credentials)) {
            $user = Auth::guard('web')->user();
            if ($user->status != 1) {
                return response()->json(['error' => '账号已被禁用'], 403);
            }
            $token = $user->createToken('Mobile')->accessToken;
            return response()->json(['token' => $token, 'user' => $user]);
        }

        return response()->json(['error' => '账号或密码错误'], 401);
    }


    // 登出
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'code' => 200,
            'msg' => '退出成功'
        ]);
    }
}