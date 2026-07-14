@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center">
    <h2>部门管理</h2>
    <button class="btn btn-primary" data-toggle="modal" data-target="#createModal">新增部门</button>
</div>

<form class="form-inline mt-3" method="GET">
    <input type="text" name="name" class="form-control mr-2" placeholder="部门名称" value="{{ request('name') }}">
    <button class="btn btn-outline-success mr-2" type="submit">搜索</button>
    <a href="{{ route('company.departments.index') }}" class="btn btn-outline-secondary">清空</a>
</form>

<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th style="width:5%">ID</th>
            <th>名称</th>
            <th style="width:10%">员工数</th>
            <th style="width:20%">操作</th>
        </tr>
    </thead>
    <tbody>
        @forelse($departments as $dept)
        <tr class="dept-row" data-dept-id="{{ $dept->id }}" style="cursor: pointer;">
            <td>{{ $dept->id }}</td>
            <td>
                <i class="fas fa-chevron-right toggle-icon mr-2" style="transition: 0.2s;"></i>
                {{ $dept->name }}
            </td>
            <td><span class="badge badge-secondary">{{ $dept->employees->count() }}</span></td>
            <td onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-info edit-btn" data-id="{{ $dept->id }}" data-name="{{ $dept->name }}">编辑</button>
                <form action="{{ route('company.departments.destroy', $dept) }}" method="POST" style="display:inline;">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger" onclick="return confirm('确定删除？')">删除</button>
                </form>
            </td>
        </tr>
        <tr class="employee-detail" id="dept-{{ $dept->id }}-employees" style="display: none;">
            <td colspan="4" class="bg-light p-0">
                <div class="p-3">
                    @if($dept->employees->isEmpty())
                        <p class="text-muted text-center mb-0">该部门暂无员工</p>
                    @else
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>姓名</th><th>邮箱</th><th>电话</th><th>状态</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dept->employees as $emp)
                                <tr>
                                    <td>{{ $emp->name }}</td>
                                    <td>{{ $emp->email }}</td>
                                    <td>{{ $emp->phone ?? '-' }}</td>
                                    <td>
                                        @if($emp->status)
                                            <span class="badge badge-success">正常</span>
                                        @else
                                            <span class="badge badge-danger">禁用</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="4" class="text-center">暂无部门</td></tr>
        @endforelse
    </tbody>
</table>
{{ $departments->links() }}

<!-- 新建部门模态框 -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('company.departments.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5>新增部门</h5></div>
                <div class="modal-body">
                    <div class="form-group"><label>名称</label><input type="text" name="name" class="form-control" required></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">保存</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- 编辑部门模态框 -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editForm" method="POST">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header"><h5>编辑部门</h5></div>
                <div class="modal-body">
                    <div class="form-group"><label>名称</label><input type="text" name="name" id="editName" class="form-control" required></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">更新</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(function(){
    // 点击部门行展开/折叠员工列表
    $('.dept-row').click(function(e){
        e.stopPropagation();
        var deptId = $(this).data('dept-id');
        var $detail = $('#dept-' + deptId + '-employees');
        var $icon = $(this).find('.toggle-icon');

        $detail.toggle(200);
        if($detail.is(':visible')){
            $icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
        } else {
            $icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
        }
    });

    // 编辑按钮点击
    $('.edit-btn').click(function(e){
        e.stopPropagation();
        var btn = $(this);
        var id = btn.data('id');
        $('#editForm').attr('action', '/company-admin/departments/' + id);
        $('#editName').val(btn.data('name'));
        $('#editModal').modal('show');
    });
});
</script>
@endsection