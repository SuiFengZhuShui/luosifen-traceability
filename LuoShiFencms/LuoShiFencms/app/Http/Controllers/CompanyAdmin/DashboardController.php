<?php

namespace App\Http\Controllers\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\User;
use App\Department;
use App\ReceivingUnit;
use App\DispatchRecord;

class DashboardController extends Controller
{
    public function index()
    {
        $enterpriseId = auth()->user()->enterprise_id;

        $stats = [
            'dispatcher_count'       => User::where('enterprise_id', $enterpriseId)->where('role', 'dispatcher')->count(),
            'department_count'       => Department::where('enterprise_id', $enterpriseId)->count(),
            'receiving_unit_count'   => ReceivingUnit::where('enterprise_id', $enterpriseId)->count(),
            'dispatch_total'         => DispatchRecord::where('enterprise_id', $enterpriseId)->count(),
            'dispatch_pending'       => DispatchRecord::where('enterprise_id', $enterpriseId)->where('status', 'pending')->count(),
            'dispatch_signed'        => DispatchRecord::where('enterprise_id', $enterpriseId)->where('status', 'signed')->count(),
        ];

        return view('company.dashboard', compact('stats'));
    }
}