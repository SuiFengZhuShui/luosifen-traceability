@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center">
    <h2>收货单位管理</h2>
    <button class="btn btn-primary" data-toggle="modal" data-target="#createModal">新增收货单位</button>
</div>

<form class="form-inline mt-3" method="GET">
    <input type="text" name="name" class="form-control mr-2" placeholder="单位名称" value="{{ request('name') }}">
    <select name="status" class="form-control mr-2">
        <option value="">全部状态</option>
        <option value="1" {{ request('status')=='1'?'selected':'' }}>启用</option>
        <option value="0" {{ request('status')=='0'?'selected':'' }}>禁用</option>
    </select>
    <button class="btn btn-outline-success mr-2" type="submit">搜索</button>
    <a href="{{ route('company.receiving-units.index') }}" class="btn btn-outline-secondary">清空</a>
</form>

<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>ID</th><th>名称</th><th>联系人</th><th>电话</th><th>地址</th><th>账号</th><th>状态</th><th>操作</th>
        </tr>
    </thead>
    <tbody>
        @forelse($units as $unit)
        <tr>
            <td>{{ $unit->id }}</td>
            <td>{{ $unit->name }}</td>
            <td>{{ $unit->contact_person }}</td>
            <td>{{ $unit->phone }}</td>
            <td>{{ $unit->address }}</td>
            <td>{{ $unit->account_name }}</td>
            <td>
                @if($unit->status) <span class="badge badge-success">启用</span> @else <span class="badge badge-danger">禁用</span> @endif
            </td>
            <td>
                <button class="btn btn-sm btn-info edit-btn"
                        data-id="{{ $unit->id }}"
                        data-name="{{ $unit->name }}"
                        data-contact="{{ $unit->contact_person }}"
                        data-phone="{{ $unit->phone }}"
                        data-address="{{ $unit->address }}"
                        data-account="{{ $unit->account_name }}"
                        data-status="{{ $unit->status }}">编辑</button>
                <button class="btn btn-sm btn-warning reset-pwd-btn" data-id="{{ $unit->id }}">重置密码</button>
                <form action="{{ route('company.receiving-units.destroy', $unit) }}" method="POST" style="display:inline;">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger" onclick="return confirm('确定删除？')">删除</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="8" class="text-center">暂无收货单位</td></tr>
        @endforelse
    </tbody>
</table>
{{ $units->links() }}

<!-- 新增模态框 -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('company.receiving-units.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5>新增收货单位</h5></div>
                <div class="modal-body">
                    <div class="form-group"><label>名称</label><input type="text" name="name" class="form-control" required></div>
                    <div class="form-group"><label>联系人</label><input type="text" name="contact_person" class="form-control"></div>
                    <div class="form-group"><label>电话</label><input type="text" name="phone" class="form-control"></div>
                    <div class="form-group"><label>地址</label><input type="text" name="address" class="form-control"></div>
                    <div class="form-group"><label>登录账号</label><input type="text" name="account_name" class="form-control"></div>
                    <div class="form-group"><label>密码</label><input type="password" name="password" class="form-control"></div>
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
                <div class="modal-header"><h5>编辑收货单位</h5></div>
                <div class="modal-body">
                    <div class="form-group"><label>名称</label><input type="text" name="name" id="editName" class="form-control" required></div>
                    <div class="form-group"><label>联系人</label><input type="text" name="contact_person" id="editContact" class="form-control"></div>
                    <div class="form-group"><label>电话</label><input type="text" name="phone" id="editPhone" class="form-control"></div>
                    <div class="form-group"><label>地址</label><input type="text" name="address" id="editAddress" class="form-control"></div>
                    <div class="form-group"><label>登录账号</label><input type="text" name="account_name" id="editAccount" class="form-control"></div>
                    <div class="form-group"><label>密码 (留空不修改)</label><input type="password" name="password" class="form-control"></div>
                    <div class="form-group"><label>状态</label>
                        <select name="status" id="editStatus" class="form-control">
                            <option value="1">启用</option>
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

<!-- 重置密码模态框 -->
<div class="modal fade" id="resetPwdModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="resetPwdForm" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5>重置密码</h5></div>
                <div class="modal-body">
                    <div class="form-group"><label>新密码</label><input type="password" name="password" class="form-control" required minlength="6"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">重置</button>
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
        $('#editForm').attr('action', '/company-admin/receiving-units/' + id);
        $('#editName').val(btn.data('name'));
        $('#editContact').val(btn.data('contact'));
        $('#editPhone').val(btn.data('phone'));
        $('#editAddress').val(btn.data('address'));
        $('#editAccount').val(btn.data('account'));
        $('#editStatus').val(btn.data('status'));
        $('#editModal').modal('show');
    });

    $('.reset-pwd-btn').click(function(){
        var id = $(this).data('id');
        $('#resetPwdForm').attr('action', '/company-admin/receiving-units/' + id + '/reset-password');
        $('#resetPwdModal').modal('show');
    });
});
</script>
@endsection