<?php

namespace App\Http\Controllers\CompanyAdmin;

use App\User;
use App\Department;
use App\AuditLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $enterpriseId = auth()->user()->enterprise_id;
        $query = User::where('enterprise_id', $enterpriseId)
                    ->where('role', 'dispatcher')
                    ->with('department');

        if ($request->filled('name')) {
            $query->where('name', 'like', '%'.$request->name.'%');
        }
        if ($request->filled('email')) {
            $query->where('email', 'like', '%'.$request->email.'%');
        }

        $employees = $query->paginate(15);
        $departments = Department::where('enterprise_id', $enterpriseId)->get();

        return view('company.employees.index', compact('employees', 'departments'));
    }

    public function store(Request $request)
    {
        $enterpriseId = auth()->user()->enterprise_id;
        $request->validate([
            'account'       => 'required|string|max:100|unique:users,account',
            'name'          => 'required|string|max:100',
            'email'         => 'nullable|email|unique:users,email',
            'phone'         => 'nullable|string',
            'password'      => 'required|min:6',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $user = User::create([
            'account'       => $request->account,
            'name'          => $request->name,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'password'      => bcrypt($request->password),
            'role'          => 'dispatcher',
            'enterprise_id' => auth()->user()->enterprise_id,
            'department_id' => $request->department_id,
            'status'        => 1,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'action' => '添加发货员',
            'description' => '账号: ' . $user->account . ', 姓名: ' . $user->name,
            'ip' => request()->ip(),
        ]);

        return back()->with('success', '员工添加成功');
    }

    public function edit(User $employee)
    {
        if ($employee->enterprise_id != auth()->user()->enterprise_id) {
            abort(403);
        }
        $departments = Department::where('enterprise_id', auth()->user()->enterprise_id)->get();
        return view('company.employees.edit', compact('employee', 'departments'));
    }

    public function update(Request $request, User $employee)
    {
        if ($employee->enterprise_id != auth()->user()->enterprise_id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,'.$employee->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|min:6',
            'department_id' => 'nullable|exists:departments,id',
            'status' => 'nullable|in:0,1',
        ]);

        $data = $request->only('name', 'email', 'phone', 'department_id', 'status');
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }
        $employee->update($data);

        AuditLog::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'action' => '更新发货员',
            'description' => '员工ID: ' . $employee->id,
            'ip' => request()->ip(),
        ]);

        return redirect()->route('company.employees.index')->with('success', '员工信息更新成功');
    }

    public function destroy(User $employee)
    {
        if ($employee->enterprise_id != auth()->user()->enterprise_id) {
            abort(403);
        }

        $employee->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'action' => '删除发货员',
            'description' => '员工ID: ' . $employee->id . ', 姓名: ' . $employee->name,
            'ip' => request()->ip(),
        ]);

        return back()->with('success', '员工已删除');
    }
}