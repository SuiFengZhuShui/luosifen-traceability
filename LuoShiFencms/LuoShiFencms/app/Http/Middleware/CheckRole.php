<?php

namespace App\Http\Middleware;

use Closure;

class CheckRole
{
    public function handle($request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        if ($user->status != 1) {
            auth()->logout();
            return redirect('/login')->withErrors('账号已被禁用');
        }

        if (!in_array($user->role, $roles)) {
            abort(403, '无权访问');
        }

        return $next($request);
    }
}