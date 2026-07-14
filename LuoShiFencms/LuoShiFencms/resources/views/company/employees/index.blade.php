@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center">
    <h2>员工管理</h2>
    <button class="btn btn-primary" data-toggle="modal" data-target="#createModal">新增发货员</button>
</div>

<form class="form-inline mt-3" method="GET">
    <input type="text" name="name" class="form-control mr-2" placeholder="姓名" value="{{ request('name') }}">
    <button class="btn btn-outline-success mr-2" type="submit">搜索</button>
    <a href="{{ route('company.employees.index') }}" class="btn btn-outline-secondary">清空</a>
</form>

<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>姓名</th>
            <th>邮箱</th>
            <th>电话</th>
            <th>部门</th>
            <th>状态</th>
            <th>操作</th>
        </tr>
    </thead>
    <tbody>
        @forelse($employees as $emp)
        <tr>
            <td>{{ $emp->name }}</td>
            <td>{{ $emp->email }}</td>
            <td>{{ $emp->phone }}</td>
            <td>{{ $emp->department->name ?? '-' }}</td>
            <td>
                @if($emp->status) <span class="badge badge-success">正常</span> @else <span class="badge badge-danger">禁用</span> @endif
            </td>
            <td>
                <button class="btn btn-sm btn-info edit-btn"
                        data-id="{{ $emp->id }}"
                        data-name="{{ $emp->name }}"
                        data-email="{{ $emp->email }}"
                        data-phone="{{ $emp->phone }}"
                        data-department="{{ $emp->department_id }}"
                        data-status="{{ $emp->status }}">编辑</button>
                <form action="{{ route('company.employees.destroy', $emp) }}" method="POST" style="display:inline;">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger" onclick="return confirm('确定删除？')">删除</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="6" class="text-center">暂无员工</td></tr>
        @endforelse
    </tbody>
</table>
{{ $employees->links() }}

<!-- 新增模态框 -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('company.employees.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5>新增发货员</h5></div>
                <div class="modal-body">
                    <div class="form-group"><label>账号</label><input type="text" name="account" class="form-control" required></div>
                    <div class="form-group"><label>姓名</label><input type="text" name="name" class="form-control" required></div>
                    <div class="form-group"><label>邮箱</label><input type="email" name="email" class="form-control" required></div>
                    <div class="form-group"><label>电话</label><input type="text" name="phone" class="form-control"></div>
                    <div class="form-group"><label>密码</label><input type="password" name="password" class="form-control" required></div>
                    <div class="form-group"><label>部门</label>
                        <select name="department_id" class="form-control">
                            <option value="">无</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">保存</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- 编辑模态框 -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editForm" method="POST">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header"><h5>编辑员工信息</h5></div>
                <div class="modal-body">
                    <div class="form-group"><label>姓名</label><input type="text" name="name" id="editName" class="form-control" required></div>
                    <div class="form-group"><label>邮箱</label><input type="email" name="email" id="editEmail" class="form-control" required></div>
                    <div class="form-group"><label>电话</label><input type="text" name="phone" id="editPhone" class="form-control"></div>
                    <div class="form-group"><label>密码 (留空不修改)</label><input type="password" name="password" class="form-control"></div>
                    <div class="form-group"><label>部门</label>
                        <select name="department_id" id="editDepartment" class="form-control">
                            <option value="">无</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group"><label>状态</label>
                        <select name="status" id="editStatus" class="form-control">
                            <option value="1">正常</option>
                            <option value="0">禁用</option>
                        </select>
                    </div>
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
    $('.edit-btn').click(function(){
        var btn = $(this);
        var id = btn.data('id');
        $('#editForm').attr('action', '/company-admin/employees/' + id);
        $('#editName').val(btn.data('name'));
        $('#editEmail').val(btn.data('email'));
        $('#editPhone').val(btn.data('phone'));
        $('#editDepartment').val(btn.data('department'));
        $('#editStatus').val(btn.data('status'));
        $('#editModal').modal('show');
    });
});
</script>
@endsection