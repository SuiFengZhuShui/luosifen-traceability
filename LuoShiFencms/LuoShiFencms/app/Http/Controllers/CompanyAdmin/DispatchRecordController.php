<?php

namespace App\Http\Controllers\CompanyAdmin;

use App\DispatchRecord;
use App\ReceivingUnit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DispatchRecordController extends Controller
{
    public function index(Request $request)
    {
        $enterpriseId = auth()->user()->enterprise_id;
        
        // 构建查询（带关联）
        $query = DispatchRecord::where('enterprise_id', $enterpriseId)
                    ->with(['dispatcher', 'department', 'receivingUnit', 'signRecord']);

        // 按批次号查询（模糊匹配）
        if ($request->filled('batch_no')) {
            $query->where('batch_no', 'like', '%'.$request->batch_no.'%');
        }

        // 按签收状态查询（pending / signed）
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 按日期范围查询（发货日期）
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // 按收货单位查询（下拉选择）
        if ($request->filled('receiving_unit')) {
            $query->where('receiving_unit_id', $request->receiving_unit);
        }

        // 按部门查询（下拉选择）
        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        // 按发货员/员工查询（下拉选择）
        if ($request->filled('employee')) {
            $query->where('user_id', $request->employee);
        }

        // 获取分页数据
        $records = $query->orderBy('created_at', 'desc')->paginate(15);

        // 获取筛选所需的下拉数据
        $receivingUnits = ReceivingUnit::where('enterprise_id', $enterpriseId)->get();
        $departments = \App\Department::where('enterprise_id', $enterpriseId)->get();
        $employees = \App\User::where('enterprise_id', $enterpriseId)->where('role', 'dispatcher')->get();

        return view('company.dispatch_records.index', compact('records', 'receivingUnits', 'departments', 'employees'));
    }
}