<?php

namespace App\Http\Controllers\CompanyAdmin;

use App\ReceivingUnit;
use App\AuditLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReceivingUnitController extends Controller
{
    public function index(Request $request)
    {
        $enterpriseId = auth()->user()->enterprise_id;
        $query = ReceivingUnit::where('enterprise_id', $enterpriseId);

        if ($request->filled('name')) {
            $query->where('name', 'like', '%'.$request->name.'%');
        }
        if ($request->filled('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $units = $query->paginate(15);
        return view('company.receiving_units.index', compact('units'));
    }

    public function store(Request $request)
    {
        $enterpriseId = auth()->user()->enterprise_id;
        $request->validate([
            'name' => 'required|string|max:100',
            'contact_person' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:200',
            'account_name' => 'nullable|string|max:50|unique:receiving_units,account_name,NULL,id,enterprise_id,'.$enterpriseId,
            'password' => 'nullable|min:6',
        ]);

        $data = $request->only('name', 'contact_person', 'phone', 'address', 'account_name');
        $data['enterprise_id'] = $enterpriseId;
        $data['status'] = 1;
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $receivingUnit = ReceivingUnit::create($data);

        AuditLog::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'action' => '新增收货单位',
            'description' => '名称: ' . $receivingUnit->name,
            'ip' => request()->ip(),
        ]);

        return back()->with('success', '收货单位添加成功');
    }

    public function edit(ReceivingUnit $receivingUnit)
    {
        if ($receivingUnit->enterprise_id != auth()->user()->enterprise_id) {
            abort(403);
        }
        return view('company.receiving_units.edit', compact('receivingUnit'));
    }

    public function update(Request $request, ReceivingUnit $receivingUnit)
    {
        if ($receivingUnit->enterprise_id != auth()->user()->enterprise_id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'contact_person' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:200',
            'account_name' => 'nullable|string|max:50|unique:receiving_units,account_name,'.$receivingUnit->id.',id,enterprise_id,'.auth()->user()->enterprise_id,
            'password' => 'nullable|min:6',
            'status' => 'nullable|in:0,1',
        ]);

        $data = $request->only('name', 'contact_person', 'phone', 'address', 'account_name', 'status');
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }
        $receivingUnit->update($data);

        AuditLog::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'action' => '修改收货单位',
            'description' => '单位ID: ' . $receivingUnit->id,
            'ip' => request()->ip(),
        ]);

        return redirect()->route('company.receiving-units.index')->with('success', '收货单位信息已更新');
    }

    public function resetPassword(Request $request, ReceivingUnit $receivingUnit)
    {
        if ($receivingUnit->enterprise_id != auth()->user()->enterprise_id) {
            abort(403);
        }

        $request->validate([
            'password' => 'required|min:6',
        ]);

        $receivingUnit->update(['password' => $request->password]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'action' => '重置收货单位密码',
            'description' => '单位ID: ' . $receivingUnit->id,
            'ip' => request()->ip(),
        ]);

        return back()->with('success', '密码重置成功');
    }

    public function destroy(ReceivingUnit $receivingUnit)
    {
        if ($receivingUnit->enterprise_id != auth()->user()->enterprise_id) {
            abort(403);
        }

        $receivingUnit->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'action' => '删除收货单位',
            'description' => '单位ID: ' . $receivingUnit->id . ', 名称: ' . $receivingUnit->name,
            'ip' => request()->ip(),
        ]);

        return back()->with('success', '收货单位已删除');
    }
}