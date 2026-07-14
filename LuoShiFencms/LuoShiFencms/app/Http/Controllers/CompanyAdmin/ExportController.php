<?php

namespace App\Http\Controllers\CompanyAdmin;

use App\Exports\DispatchRecordsExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function export()
    {
        $enterpriseId = auth()->user()->enterprise_id;
        return Excel::download(new DispatchRecordsExport($enterpriseId), '发货记录_'.now()->format('YmdHis').'.xlsx');
    }
}