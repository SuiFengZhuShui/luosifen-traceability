<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>发货员工作台</title>
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.5.0/css/bootstrap.min.css">
    <style>
        .card { margin-bottom: 12px; }
        .badge-pending { background: #ffc107; color: #212529; }
        .badge-signed { background: #28a745; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5>🚚 发货员工作台</h5>
            <span class="text-muted">{{ auth()->user()->name }}</span>
        </div>

        <div class="mb-3">
            <button class="btn btn-primary btn-block" disabled>📷 新建发货 (开发中)</button>
        </div>

        <h6>我的发货记录</h6>
        @forelse($records as $rec)
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>{{ $rec->product_name }}</h6>
                        <p class="mb-1"><small>批次号：{{ $rec->batch_no }}</small></p>
                        <p class="mb-1"><small>数量：{{ $rec->quantity }}</small></p>
                        <p class="mb-0"><small>收货单位：{{ $rec->receivingUnit->name ?? '-' }}</small></p>
                    </div>
                    <div>
                        @if($rec->status === 'signed')
                            <span class="badge badge-signed">✓ 已签收</span>
                        @else
                            <span class="badge badge-pending">待签收</span>
                        @endif
                    </div>
                </div>
            </div>
            @if($rec->qrcode_path)
            <div class="card-footer text-center">
                <img src="{{ Storage::url($rec->qrcode_path) }}" style="max-height: 120px;" alt="签收码">
                <p class="mt-1 mb-0"><small>签收二维码</small></p>
            </div>
            @endif
        </div>
        @empty
        <div class="text-center text-muted py-5">
            <p>暂无发货记录</p>
        </div>
        @endforelse

        <div class="mt-4 text-center">
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="btn btn-outline-danger btn-sm">退出登录</a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    </div>
</body>
</html>