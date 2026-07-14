@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>发货记录查询</h2>
    <a href="{{ route('company.dispatch.export', request()->query()) }}" class="btn btn-success">导出 Excel</a>
</div>

<!-- 筛选表单 -->
<form method="GET" class="card card-body mb-3">
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label>批次号</label>
                <input type="text" name="batch_no" class="form-control" placeholder="输入批次号" value="{{ request('batch_no') }}">
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label>签收状态</label>
                <select name="status" class="form-control">
                    <option value="">全部</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>待签收</option>
                    <option value="signed" {{ request('status') == 'signed' ? 'selected' : '' }}>已签收</option>
                </select>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label>开始日期</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label>截止日期</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>收货单位</label>
                <select name="receiving_unit" class="form-control">
                    <option value="">全部</option>
                    @foreach($receivingUnits as $unit)
                        <option value="{{ $unit->id }}" {{ request('receiving_unit') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>部门</label>
                <select name="department" class="form-control">
                    <option value="">全部</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>发货员</label>
                <select name="employee" class="form-control">
                    <option value="">全部</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>&nbsp;</label>
                <div>
                    <button type="submit" class="btn btn-primary">查询</button>
                    <a href="{{ route('company.dispatch.index') }}" class="btn btn-secondary">重置</a>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- 数据表格 -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>销售单号</th>
                        <th>购买方</th>
                        <th>产品</th>
                        <th>规格</th>
                        <th>数量</th>
                        <th>批次号</th>
                        <th>生产日期</th>
                        <th>发货员</th>
                        <th>部门</th>
                        <th>收货单位</th>
                        <th>状态</th>
                        <th>签收信息</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $rec)
                    <tr>
                        <td>{{ $rec->id }}</td>
                        <td>{{ $rec->sales_order_no }}</td>
                        <td>{{ $rec->buyer_name }}</td>
                        <td>{{ $rec->product_name }}</td>
                        <td>{{ $rec->spec }}</td>
                        <td>{{ $rec->quantity }}</td>
                        <td>{{ $rec->batch_no }}</td>
                        <td>{{ $rec->production_date }}</td>
                        <td>{{ $rec->dispatcher->name ?? '' }}</td>
                        <td>{{ $rec->department->name ?? '' }}</td>
                        <td>{{ $rec->receivingUnit->name ?? '' }}</td>
                        <td>
                            @if($rec->status == 'signed')
                                <span class="badge badge-success">已签收</span>
                            @else
                                <span class="badge badge-warning">待签收</span>
                            @endif
                        </td>
                        <td>
                            @if($rec->signRecord)
                                {{ $rec->signRecord->receiver_name }} / {{ $rec->signRecord->actual_quantity }}件
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="text-center py-4">暂无数据</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($records->hasPages())
    <div class="card-footer d-flex justify-content-center">
        {{ $records->links() }}
    </div>
    @endif
</div>
@endsection