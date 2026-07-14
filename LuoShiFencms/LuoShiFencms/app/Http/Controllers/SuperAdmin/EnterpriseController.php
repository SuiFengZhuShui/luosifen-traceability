<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enterprise;
use App\User;
use App\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class EnterpriseController extends Controller
{
    public function index()
    {
        $enterprises = Enterprise::with('users')->paginate(15);
        return view('super.enterprises.index', compact('enterprises'));
    }

    public function create()
    {
        return view('super.enterprises.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'contact' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
        ]);

        $enterprise = Enterprise::create($request->only('name', 'contact', 'phone'));

        AuditLog::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'action' => '创建企业',
            'description' => '企业名称: ' . $enterprise->name,
            'ip' => request()->ip(),
        ]);

        return redirect()->route('super.enterprises.index')->with('success', '企业创建成功');
    }

    public function show(Enterprise $enterprise)
    {
        return view('super.enterprises.show', compact('enterprise'));
    }

    public function edit(Enterprise $enterprise)
    {
        return view('super.enterprises.edit', compact('enterprise'));
    }

    public function update(Request $request, Enterprise $enterprise)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'contact' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'status' => 'nullable|in:0,1',
        ]);

        $enterprise->update($request->only('name', 'contact', 'phone', 'status'));

        AuditLog::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'action' => '更新企业',
            'description' => '企业ID: ' . $enterprise->id,
            'ip' => request()->ip(),
        ]);

        return redirect()->route('super.enterprises.index')->with('success', '企业信息已更新');
    }

    public function destroy(Enterprise $enterprise)
    {
        DB::transaction(function () use ($enterprise) {
            $enterprise->delete();

            AuditLog::create([
                'user_id'     => auth()->id(),
                'user_name'   => auth()->user()->name,
                'action'      => '删除企业',
                'description' => '企业ID: ' . $enterprise->id . ', 名称: ' . $enterprise->name,
                'ip'          => request()->ip(),
            ]);
        });

        return redirect()->route('super.enterprises.index')->with('success', '企业已删除');
    }

    // 为企业创建管理员账号
    public function createAccount(Request $request, Enterprise $enterprise)
    {
        $request->validate([
            'account'  => 'required|string|max:100|unique:users,account',
            'name'     => 'required|string|max:100',
            'email'    => 'nullable|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'account'       => $request->account,
            'name'          => $request->name,
            'phone'         => $request->phone, 
            'email'         => $request->email,
            'password'      => bcrypt($request->password),
            'role'          => 'company_admin',
            'enterprise_id' => $enterprise->id,
            'status'        => 1,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'action' => '创建企业管理员账号',
            'description' => '账号: ' . $user->account . ', 企业ID: ' . $enterprise->id,
            'ip' => request()->ip(),
        ]);

        return back()->with('success', '企业管理员账号创建成功');
    }

    /**
     * 切换企业状态 (启用/禁用)
     */
   public function toggleStatus(Request $request, Enterprise $enterprise)
    {
        $enterprise->status = $enterprise->status ? 0 : 1;
        $enterprise->save();

        AuditLog::create([
            'user_id'     => auth()->id(),
            'user_name'   => auth()->user()->name,
            'action'      => '切换企业状态',
            'description' => '企业ID: ' . $enterprise->id . ' 新状态: ' . ($enterprise->status ? '启用' : '禁用'),
            'ip'          => request()->ip(),
            'created_at'  => now(),  
        ]);

        return response()->json([
            'success' => true,
            'status'  => $enterprise->status,
            'message' => '状态已更新',
        ]);
    }

    /**
     * 删除企业管理员
     */
    public function deleteAdmin(Enterprise $enterprise, User $admin)
    {
        // 确保被删除的用户属于该企业且是企业管理员角色
        if ($admin->enterprise_id != $enterprise->id || $admin->role != 'company_admin') {
            abort(403, '无权操作');
        }

        $admin->delete();

        AuditLog::create([
            'user_id'     => auth()->id(),
            'user_name'   => auth()->user()->name,
            'action'      => '删除企业管理员',
            'description' => '管理员账号: ' . $admin->account . ', 企业: ' . $enterprise->name,
            'ip'          => request()->ip(),
            'created_at'  => now(),
        ]);

        return back()->with('success', '管理员已删除');
    }
}