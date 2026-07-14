<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Enterprise;
use App\User;
use App\DispatchRecord;
use App\SignRecord;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'enterprise_count'         => Enterprise::count(),
            'company_admin_count'      => User::where('role', 'company_admin')->count(),
            'dispatcher_count'         => User::where('role', 'dispatcher')->count(),
            'dispatch_record_count'    => DispatchRecord::count(),
            'pending_dispatch_count'   => DispatchRecord::where('status', 'pending')->count(),
            'signed_dispatch_count'    => DispatchRecord::where('status', 'signed')->count(),
            'sign_record_count'        => SignRecord::count(),
        ];

        $enterprises = Enterprise::withCount(['users as admin_count' => function ($query) {
            $query->where('role', 'company_admin');
        }])->orderBy('name')->get();

        return view('super.dashboard', compact('stats', 'enterprises'));
    }
}