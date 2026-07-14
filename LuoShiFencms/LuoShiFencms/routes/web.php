<?php

Route::get('/', function () {
    return redirect('/login');
});

Auth::routes();

// 超级管理员
Route::prefix('super-admin')->middleware(['auth', 'role:super_admin'])->name('super.')->group(function () {
    Route::get('/dashboard', 'SuperAdmin\DashboardController@index')->name('dashboard');
    
    Route::delete('enterprises/{enterprise}/admin/{admin}', 'SuperAdmin\EnterpriseController@deleteAdmin')
         ->name('enterprises.delete-admin');
    Route::post('enterprises/{enterprise}/account', 'SuperAdmin\EnterpriseController@createAccount')
         ->name('enterprises.account');
    Route::patch('enterprises/{enterprise}/toggle-status', 'SuperAdmin\EnterpriseController@toggleStatus')
         ->name('enterprises.toggle-status');
         
    Route::resource('enterprises', 'SuperAdmin\EnterpriseController');
});

// 企业管理员
Route::prefix('company-admin')->middleware(['auth', 'role:company_admin'])->name('company.')->group(function () {
    Route::get('/dashboard', 'CompanyAdmin\DashboardController@index')->name('dashboard');
    Route::resource('employees', 'CompanyAdmin\EmployeeController');
    Route::resource('departments', 'CompanyAdmin\DepartmentController');
    Route::resource('receiving-units', 'CompanyAdmin\ReceivingUnitController');
    Route::post('receiving-units/{unit}/reset-password', 'CompanyAdmin\ReceivingUnitController@resetPassword')->name('receiving-units.reset-password');
    Route::get('dispatch-records', 'CompanyAdmin\DispatchRecordController@index')->name('dispatch.index');
    Route::get('dispatch-records/export', 'CompanyAdmin\ExportController@export')->name('dispatch.export');
});

// 签收页面（免登录）
Route::get('/sign/{id}', 'Api\SignController@show')->name('sign.show');
Route::post('/sign/{id}', 'Api\SignController@store')->name('sign.store');

// 移动端发货员（暂时展示主页）
Route::prefix('mobile')->middleware(['auth', 'role:dispatcher'])->group(function () {
    Route::get('/home', function () {
        $records = \App\DispatchRecord::where('user_id', auth()->id())
                    ->with('receivingUnit:id,name')
                    ->latest()
                    ->get();
        return view('mobile.dispatcher.home', compact('records'));
    })->name('mobile.home');
});