@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>企业管理</h2>
    <button class="btn btn-primary" data-toggle="modal" data-target="#createModal">新建企业</button>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th style="width:5%">ID</th>
                        <th>企业名称</th>
                        <th>联系人</th>
                        <th>电话</th>
                        <th style="width:8%">管理员</th>
                        <th style="width:10%">状态</th>
                        <th style="width:28%">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enterprises as $ent)
                    @php
                        $admins = $ent->users->where('role', 'company_admin');
                        $hasAdmins = $admins->count() > 0;
                    @endphp
                    <!-- 企业主行 -->
                    <tr class="enterprise-row {{ $hasAdmins ? 'clickable' : '' }}" 
                        data-enterprise-id="{{ $ent->id }}"
                        @if($hasAdmins) style="cursor:pointer;" @endif>
                        <td>{{ $ent->id }}</td>
                        <td>
                            <strong>{{ $ent->name }}</strong>
                            @if($hasAdmins)
                                <span class="toggle-icon ml-2"></span>
                            @endif
                        </td>
                        <td>{{ $ent->contact ?? '-' }}</td>
                        <td>{{ $ent->phone ?? '-' }}</td>
                        <td><span class="badge badge-info">{{ $admins->count() }}</span></td>
                        <td>
                            <button class="btn btn-sm toggle-status-btn {{ $ent->status ? 'btn-success' : 'btn-secondary' }}"
                                    data-id="{{ $ent->id }}"
                                    data-status="{{ $ent->status }}">
                                {{ $ent->status ? '启用' : '禁用' }}
                            </button>
                        </td>
                        <td onclick="event.stopPropagation();">
                            <a href="{{ route('super.enterprises.edit', $ent) }}" class="btn btn-sm btn-outline-info">编辑</a>
                            <button class="btn btn-sm btn-outline-warning account-btn" data-id="{{ $ent->id }}">创建管理员</button>
                            <form action="{{ route('super.enterprises.destroy', $ent) }}" method="POST" class="d-inline" onsubmit="return confirm('确定删除该企业吗？')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">删除</button>
                            </form>
                        </td>
                    </tr>

                    <!-- 管理员子行（与企业管理员-部门管理的员工展开样式一致） -->
                    @if($hasAdmins)
                    <tr class="admin-detail" id="admin-{{ $ent->id }}" style="display: none;">
                        <td colspan="7" class="bg-light p-0">
                            <div class="p-3">
                                <table class="table table-sm table-borderless mb-0" style="background: transparent;">
                                    <thead>
                                        <tr>
                                            <th>账号</th><th>姓名</th><th>邮箱</th><th>电话</th><th>状态</th><th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($admins as $admin)
                                        <tr>
                                            <td>{{ $admin->account }}</td>
                                            <td>{{ $admin->name }}</td>
                                            <td>{{ $admin->email ?? '-' }}</td>
                                            <td>{{ $admin->phone ?? '-' }}</td>
                                            <td>
                                                @if($admin->status)
                                                    <span class="badge badge-success">正常</span>
                                                @else
                                                    <span class="badge badge-danger">禁用</span>
                                                @endif
                                            </td>
                                            <td>
                                                <form action="{{ route('super.enterprises.delete-admin', [$ent, $admin]) }}" method="POST" onsubmit="return confirm('确定删除该管理员吗？')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger">删除</button>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                    @endif
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">暂无企业数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($enterprises->hasPages())
    <div class="card-footer d-flex justify-content-center">
        {{ $enterprises->links() }}
    </div>
    @endif
</div>

<!-- 新建企业模态框 -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('super.enterprises.store') }}" method="POST">
        @csrf
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">新建企业</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group"><label>企业名称</label><input type="text" name="name" class="form-control" required></div>
                <div class="form-group"><label>联系人</label><input type="text" name="contact" class="form-control"></div>
                <div class="form-group"><label>电话</label><input type="text" name="phone" class="form-control"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-primary">保存</button>
            </div>
        </div>
        </form>
    </div>
</div>

<!-- 创建管理员模态框 -->
<div class="modal fade" id="accountModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="accountForm" method="POST">
        @csrf
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">创建企业管理员账号</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group"><label>账号</label><input type="text" name="account" class="form-control" required></div>
                <div class="form-group"><label>姓名</label><input type="text" name="name" class="form-control" required></div>
                <div class="form-group"><label>电话（选填）</label><input type="text" name="phone" class="form-control"></div>
                <div class="form-group"><label>邮箱（选填）</label><input type="email" name="email" class="form-control"></div>
                <div class="form-group"><label>密码</label><input type="password" name="password" class="form-control" required minlength="6"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-warning">创建</button>
            </div>
        </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(function(){
    // 点击企业行展开/折叠管理员列表（样式与企业管理员-部门管理中的员工展开一致）
    $('.enterprise-row.clickable').click(function(e){
        e.stopPropagation();
        var entId = $(this).data('enterprise-id');
        var $detail = $('#admin-' + entId);
        $detail.toggle(200);
    });

    // 创建管理员按钮
    $('.account-btn').click(function(e){
        e.stopPropagation();
        var id = $(this).data('id');
        $('#accountForm').attr('action', '/super-admin/enterprises/' + id + '/account');
        $('#accountModal').modal('show');
    });

    // 切换状态按钮
    $('.toggle-status-btn').click(function(e) {
        e.stopPropagation();
        var btn = $(this);
        var id = btn.data('id');

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: '/super-admin/enterprises/' + id + '/toggle-status',
            type: 'PATCH',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    var newStatus = response.status;
                    btn.removeClass(newStatus ? 'btn-secondary' : 'btn-success')
                       .addClass(newStatus ? 'btn-success' : 'btn-secondary')
                       .text(newStatus ? '启用' : '禁用')
                       .prop('disabled', false);
                    btn.data('status', newStatus);
                }
            },
            error: function() {
                btn.prop('disabled', false).text('操作失败');
            }
        });
    });
});
</script>
@endsection