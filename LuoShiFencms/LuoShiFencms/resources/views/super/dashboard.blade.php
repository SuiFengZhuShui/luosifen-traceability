@extends('layouts.app')

@section('content')
<h2>超级管理员控制台</h2>

<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary mb-3 dashboard-card">
            <div class="card-header">企业总数</div>
            <div class="card-body">
                <h5 class="card-title">{{ $stats['enterprise_count'] }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success mb-3 dashboard-card">
            <div class="card-header">企业管理员</div>
            <div class="card-body">
                <h5 class="card-title">{{ $stats['company_admin_count'] }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info mb-3 dashboard-card">
            <div class="card-header">发货员</div>
            <div class="card-body">
                <h5 class="card-title">{{ $stats['dispatcher_count'] }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning mb-3 dashboard-card">
            <div class="card-header">待签收</div>
            <div class="card-body">
                <h5 class="card-title">{{ $stats['pending_dispatch_count'] }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger mb-3 dashboard-card">
            <div class="card-header">已签收</div>
            <div class="card-body">
                <h5 class="card-title">{{ $stats['signed_dispatch_count'] }}</h5>
            </div>
        </div>
    </div>

<div class="table-responsive">
    <table class="table table-hover">
        <thead class="thead-light">
            <tr>
                <th>企业名称</th>
                <th>联系人</th>
                <th>电话</th>
                <th>管理员数</th>
                <th>状态</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            @forelse($enterprises as $ent)
            <tr>
                <td>{{ $ent->name }}</td>
                <td>{{ $ent->contact }}</td>
                <td>{{ $ent->phone }}</td>
                <td>{{ $ent->admin_count }}</td>
                <td>
                    @if($ent->status)
                        <span class="badge badge-success">启用</span>
                    @else
                        <span class="badge badge-danger">禁用</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('super.enterprises.edit', $ent) }}" class="btn btn-sm btn-outline-secondary">编辑</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center">暂无企业</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection