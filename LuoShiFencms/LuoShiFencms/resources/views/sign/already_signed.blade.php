<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>已完成签收</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f0f4ff; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 20px; 
        }
        .card { 
            background: #fff; 
            border-radius: 16px; 
            padding: 40px 24px; 
            text-align: center; 
            max-width: 400px; 
        }
        .icon { font-size: 60px; margin-bottom: 16px; }
        .title { font-size: 20px; font-weight: bold; color: #00b42a; margin-bottom: 8px; }
        .desc { font-size: 14px; color: #999; margin-bottom: 16px; }
        .info { font-size: 13px; color: #666; margin-bottom: 4px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">✅</div>
        <div class="title">该记录已完成签收</div>
        <div class="desc">此发货记录已签收，无需重复操作</div>
        @if(isset($signRecord))
        <div class="info">签收人：{{ $signRecord->receiver_name ?? '-' }}</div>
        <div class="info">签收时间：{{ $signRecord->signed_at ?? '-' }}</div>
        @endif
    </div>
</body>
</html>