@extends('layouts.app')

@section('content')
<div class="container">
    <h2>编辑企业信息</h2>

    <form action="{{ route('super.enterprises.update', $enterprise) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>企业名称</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $enterprise->name) }}" required>
        </div>
        <div class="form-group">
            <label>联系人</label>
            <input type="text" name="contact" class="form-control" value="{{ old('contact', $enterprise->contact) }}">
        </div>
        <div class="form-group">
            <label>电话</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone', $enterprise->phone) }}">
        </div>
        <div class="form-group">
            <label>状态</label>
            <select name="status" class="form-control">
                <option value="1" {{ $enterprise->status == 1 ? 'selected' : '' }}>启用</option>
                <option value="0" {{ $enterprise->status == 0 ? 'selected' : '' }}>禁用</option>
            </select>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">更新</button>
            <a href="{{ route('super.dashboard') }}" class="btn btn-secondary">返回</a>
        </div>
    </form>
</div>
@endsection