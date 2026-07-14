<?php

namespace App\Http\Controllers\CompanyAdmin;

use App\Department;
use App\AuditLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $enterpriseId = auth()->user()->enterprise_id;
        $query = Department::where('enterprise_id', $enterpriseId)
                    ->with('employees');
        if ($request->filled('name')) {
            $query->where('name', 'like', '%'.$request->name.'%');
        }
        $departments = $query->paginate(15);
        return view('company.departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $enterpriseId = auth()->user()->enterprise_id;
        $department = Department::create([
            'enterprise_id' => $enterpriseId,
            'name' => $request->name,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'action' => '新增部门',
            'description' => '名称: ' . $department->name,
            'ip' => request()->ip(),
        ]);

        return back()->with('success', '部门创建成功');
    }

    public function edit(Department $department)
    {
        if ($department->enterprise_id != auth()->user()->enterprise_id) {
            abort(403);
        }
        return view('company.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        if ($department->enterprise_id != auth()->user()->enterprise_id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $department->update(['name' => $request->name]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'action' => '修改部门',
            'description' => '部门ID: ' . $department->id . ', 新名称: ' . $department->name,
            'ip' => request()->ip(),
        ]);

        return redirect()->route('company.departments.index')->with('success', '部门信息已更新');
    }

    public function destroy(Department $department)
    {
        if ($department->enterprise_id != auth()->user()->enterprise_id) {
            abort(403);
        }

        $department->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'action' => '删除部门',
            'description' => '部门ID: ' . $department->id . ', 名称: ' . $department->name,
            'ip' => request()->ip(),
        ]);

        return back()->with('success', '部门已删除');
    }
}