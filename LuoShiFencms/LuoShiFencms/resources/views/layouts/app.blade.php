<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>螺蛳粉企业溯源管理系统</title>
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.5.0/css/bootstrap.min.css">
    <style>
        body { padding-top: 70px; }
        .navbar { position: fixed; top: 0; width: 100%; z-index: 1000; }
         .dashboard-card {
        min-height: 140px;          /* 统一所有卡片的最小高度 */
        display: flex;
        flex-direction: column;
        }
        .dashboard-card .card-body {
            flex: 1;                   /* 让 card-body 撑满剩余空间 */
            display: flex;
            flex-direction: column;
            justify-content: center;   /* 内容垂直居中 */
        }
        .dashboard-card .card-body h5 {
            margin-bottom: 0.5rem;
        }
    </style>
    @yield('style')
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">螺狮粉企业溯源系统</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                @auth
                    @if(auth()->user()->isSuperAdmin())
                        <li class="nav-item"><a class="nav-link" href="{{ route('super.dashboard') }}">控制台</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('super.enterprises.index') }}">企业管理</a></li>
                    @elseif(auth()->user()->isCompanyAdmin())
                        <li class="nav-item"><a class="nav-link" href="{{ route('company.dashboard') }}">控制台</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('company.employees.index') }}">员工管理</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('company.departments.index') }}">部门管理</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('company.receiving-units.index') }}">收货单位</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('company.dispatch.index') }}">发货记录</a></li>
                    @endif
                @endauth
            </ul>
            <ul class="navbar-nav ml-auto">
                @guest
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">登录</a></li>
                @else
                    <li class="nav-item"><span class="navbar-text text-light mr-3">{{ auth()->user()->name }}</span></li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">退出</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>

<main class="container">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @yield('content')
</main>

<script src="https://cdn.bootcdn.net/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.bundle.min.js"></script>
@yield('scripts')
<script>
function showToast(message, type) {
    var cls = type === 'success' ? 'alert-success' : 'alert-danger';
    var html = '<div class="alert '+cls+' alert-dismissible fade show position-fixed" style="top:70px; right:20px; z-index:9999;">'
             + message
             + '<button type="button" class="close" data-dismiss="alert">&times;</button>'
             + '</div>';
    var $alert = $(html).appendTo('body');
    setTimeout(function(){
        $alert.alert('close');
    }, 2000);
}
</script>
</body>
</html>