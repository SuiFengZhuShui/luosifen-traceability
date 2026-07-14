@extends('layouts.app')

@section('content')
<h2>企业管理员控制台</h2>

<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary mb-3 dashboard-card">
            <div class="card-header">发货员</div>
            <div class="card-body">
                <h5 class="card-title">{{ $stats['dispatcher_count'] }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success mb-3 dashboard-card">
            <div class="card-header">部门</div>
            <div class="card-body">
                <h5 class="card-title">{{ $stats['department_count'] }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info mb-3 dashboard-card">
            <div class="card-header">收货单位</div>
            <div class="card-body">
                <h5 class="card-title">{{ $stats['receiving_unit_count'] }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning mb-3 dashboard-card">
            <div class="card-header">发货记录</div>
            <div class="card-body">
                <h5 class="card-title">{{ $stats['dispatch_total'] }}</h5>
                <p class="small mb-0">待签收 {{ $stats['dispatch_pending'] }} · 已签收 {{ $stats['dispatch_signed'] }}</p>
            </div>
        </div>
    </div>
</div>
@endsection