<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    // 显示登录表单
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // 处理登录
    public function login(Request $request)
    {
        $request->validate([
            'account'  => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::guard('web')->attempt([
            'account'  => $request->account,
            'password' => $request->password,
            'status'   => 1,  // 只允许状态正常的用户登录
        ])) {
            $user = Auth::guard('web')->user();
            if ($user->isSuperAdmin()) {
                return redirect('/super-admin/dashboard');
            } elseif ($user->isCompanyAdmin()) {
                return redirect('/company-admin/dashboard');
            } elseif ($user->isDispatcher()) {
                return redirect('/mobile/home');
            }
            return redirect('/');
        }

        return back()->withErrors(['account' => '账号或密码错误']);
    }

    // 退出
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}