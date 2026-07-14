<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>收货签收</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f0f4ff; 
            min-height: 100vh; 
            padding: 20px; 
        }
        .card { 
            background: #fff; 
            border-radius: 16px; 
            padding: 24px; 
            max-width: 500px; 
            margin: 0 auto; 
        }
        .title { 
            font-size: 22px; 
            font-weight: bold; 
            text-align: center; 
            margin-bottom: 24px; 
            color: #333; 
        }
        
        /* 发货信息 */
        .info-section { 
            background: #f9f9f9; 
            border-radius: 12px; 
            padding: 16px; 
            margin-bottom: 24px; 
        }
        .info-row { 
            display: flex; 
            justify-content: space-between; 
            padding: 8px 0; 
            border-bottom: 1px solid #eee; 
            font-size: 14px; 
        }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #999; }
        .info-value { color: #333; font-weight: 500; }

        /* 表单 */
        .form-item { margin-bottom: 20px; }
        .form-label { 
            font-size: 15px; 
            color: #333; 
            margin-bottom: 8px; 
            display: block; 
            font-weight: 500; 
        }
        .form-input { 
            width: 100%; 
            border: 1px solid #ddd; 
            border-radius: 8px; 
            padding: 12px; 
            font-size: 16px; 
            background: #fafafa; 
        }

        /* 签名区 */
        .signature-area { 
            border: 1px solid #ddd; 
            border-radius: 8px; 
            overflow: hidden; 
            background: #fff; 
        }
        #signCanvas { 
            width: 100%; 
            height: 200px; 
            display: block; 
            touch-action: none; 
        }
        .signature-actions { 
            text-align: right; 
            margin-top: 8px; 
        }
        .clear-btn { 
            color: #007aff; 
            font-size: 14px; 
            border: none; 
            background: none; 
            padding: 8px 16px; 
            cursor: pointer; 
        }

        /* 提交按钮 */
        .submit-btn { 
            width: 100%; 
            background: linear-gradient(135deg, #3a7bd5, #00d2ff); 
            color: #fff; 
            border: none; 
            border-radius: 25px; 
            padding: 14px; 
            font-size: 18px; 
            font-weight: bold; 
            margin-top: 16px; 
            cursor: pointer; 
        }
        .submit-btn:active { opacity: 0.8; }
        .submit-btn.loading { opacity: 0.6; pointer-events: none; }

        /* 提示消息 */
        .toast { 
            position: fixed; 
            top: 20px; 
            left: 50%; 
            transform: translateX(-50%); 
            background: rgba(0,0,0,0.75); 
            color: #fff; 
            padding: 12px 24px; 
            border-radius: 8px; 
            font-size: 14px; 
            z-index: 9999; 
            display: none; 
        }
        .toast.success { background: #00b42a; }
        .toast.error { background: #e74c3c; }
    </style>
</head>
<body>
    <div class="card">
        <div class="title">收货签收</div>

        <!-- 发货信息 -->
        <div class="info-section">
            <div class="info-row">
                <span class="info-label">销售单号</span>
                <span class="info-value">{{ $dispatch->sales_order_no ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">产品名称</span>
                <span class="info-value">{{ $dispatch->product_name ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">发货数量</span>
                <span class="info-value">{{ $dispatch->quantity ?? 0 }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">收货单位</span>
                <span class="info-value">{{ $dispatch->receivingUnit->name ?? '-' }}</span>
            </div>
        </div>

        <!-- 签收表单 -->
        <form id="signForm" onsubmit="return false;">
            <div class="form-item">
                <label class="form-label">实收数量 *</label>
                <input type="number" class="form-input" id="actualQuantity" value="{{ $dispatch->quantity ?? 0 }}" required>
            </div>
            <div class="form-item">
                <label class="form-label">收货人姓名 *</label>
                <input type="text" class="form-input" id="receiverName" placeholder="请输入收货人姓名" required>
            </div>
            <div class="form-item">
                <label class="form-label">手机号（选填）</label>
                <input type="tel" class="form-input" id="receiverPhone" maxlength="11" placeholder="请输入手机号">
            </div>
            <div class="form-item">
                <label class="form-label">手写签名 *</label>
                <div class="signature-area">
                    <canvas id="signCanvas"></canvas>
                </div>
                <div class="signature-actions">
                    <button type="button" class="clear-btn" onclick="clearCanvas()">清除重签</button>
                </div>
            </div>
            <button type="button" class="submit-btn" id="submitBtn" onclick="submitSign()">确认签收</button>
        </form>
    </div>

    <!-- 提示消息 -->
    <div class="toast" id="toast"></div>

    <script>
        var canvas = document.getElementById('signCanvas');
        var ctx = canvas.getContext('2d');
        var drawing = false;

        // 设置画布大小
        canvas.width = canvas.offsetWidth;
        canvas.height = 200;
        
        ctx.strokeStyle = '#333';
        ctx.lineWidth = 3;
        ctx.lineCap = 'round';

        // 鼠标事件
        canvas.addEventListener('mousedown', start);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stop);
        canvas.addEventListener('mouseleave', stop);

        // 触摸事件
        canvas.addEventListener('touchstart', function(e) { e.preventDefault(); start(e.touches[0]); });
        canvas.addEventListener('touchmove', function(e) { e.preventDefault(); draw(e.touches[0]); });
        canvas.addEventListener('touchend', stop);

        function getPos(e) {
            var rect = canvas.getBoundingClientRect();
            return { x: e.clientX - rect.left, y: e.clientY - rect.top };
        }

        function start(e) {
            drawing = true;
            var pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        }

        function draw(e) {
            if (!drawing) return;
            var pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        }

        function stop() {
            drawing = false;
        }

        function clearCanvas() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }

        function getSignature() {
            return canvas.toDataURL('image/png');
        }

        function showToast(msg, type) {
            var toast = document.getElementById('toast');
            toast.textContent = msg;
            toast.className = 'toast ' + (type || '');
            toast.style.display = 'block';
            setTimeout(function() { toast.style.display = 'none'; }, 2000);
        }

        function submitSign() {
            var quantity = document.getElementById('actualQuantity').value;
            var name = document.getElementById('receiverName').value;
            var phone = document.getElementById('receiverPhone').value;

            if (!quantity || quantity <= 0) {
                showToast('请输入实收数量', 'error');
                return;
            }
            if (!name) {
                showToast('请输入收货人姓名', 'error');
                return;
            }

            var signature = getSignature();
            var btn = document.getElementById('submitBtn');
            btn.textContent = '提交中...';
            btn.classList.add('loading');

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/sign/{{ $dispatch->id }}', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('Accept', 'application/json');

            xhr.onload = function() {
                btn.textContent = '确认签收';
                btn.classList.remove('loading');

                if (xhr.status === 200) {
                    var res = JSON.parse(xhr.responseText);
                    if (res.code === 200) {
                        showToast('签收成功', 'success');
                        setTimeout(function() { location.reload(); }, 1500);
                    } else {
                        showToast(res.message || '签收失败', 'error');
                    }
                } else {
                    showToast('网络错误', 'error');
                }
            };

            xhr.onerror = function() {
                btn.textContent = '确认签收';
                btn.classList.remove('loading');
                showToast('网络错误', 'error');
            };

            xhr.send(JSON.stringify({
                actual_quantity: parseInt(quantity),
                receiver_name: name,
                receiver_phone: phone,
                signature: signature
            }));
        }
    </script>
</body>
</html>