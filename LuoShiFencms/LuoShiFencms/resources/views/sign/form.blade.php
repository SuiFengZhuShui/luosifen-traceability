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

        /* 签名预览区 - 点击放大 */
        .signature-preview {
            border: 2px dashed #ccc;
            border-radius: 8px;
            background: #fafafa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 120px;
            overflow: hidden;
            cursor: pointer;
        }
        .sign-preview-img { width: 100%; display: block; }
        .sign-placeholder { text-align: center; padding: 30px 0; }
        .sign-placeholder-icon { font-size: 40px; display: block; margin-bottom: 8px; }
        .sign-placeholder-text { font-size: 15px; color: #999; }
        .signature-actions { text-align: right; margin-top: 8px; }
        .resign-btn {
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

        /* 全屏签名弹窗 */
        .sign-modal {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: #fff;
            z-index: 9999;
            display: none;
            flex-direction: column;
        }
        .sign-modal.active { display: flex; }
        .sign-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
        }
        .modal-title { font-size: 18px; font-weight: bold; }
        .modal-close { font-size: 16px; color: #999; border: none; background: none; cursor: pointer; }
        .sign-modal-body {
            flex: 1;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        #signCanvas {
            width: 100%;
            max-width: 500px;
            height: 300px;
            border: 1px solid #ddd;
            border-radius: 8px;
            touch-action: none;
            display: block;
            margin: 0 auto;
        }
        .sign-modal-footer {
            display: flex;
            gap: 12px;
            padding: 15px 20px 30px;
        }
        .modal-btn {
            flex: 1;
            height: 46px;
            line-height: 46px;
            text-align: center;
            border-radius: 23px;
            font-size: 16px;
            border: none;
            cursor: pointer;
        }
        .btn-clear { background: #f0f0f0; color: #666; }
        .btn-confirm { background: linear-gradient(135deg, #3a7bd5, #00d2ff); color: #fff; }

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
            z-index: 99999;
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
                <div class="signature-preview" id="signPreview" onclick="openSignModal()">
                    <img id="signPreviewImg" class="sign-preview-img" style="display:none;" />
                    <div id="signPlaceholder" class="sign-placeholder">
                        <span class="sign-placeholder-icon">✍️</span>
                        <span class="sign-placeholder-text">点击此处手写签名</span>
                    </div>
                </div>
                <div class="signature-actions" id="signActions" style="display:none;">
                    <button type="button" class="resign-btn" onclick="openSignModal()">重新签名</button>
                </div>
            </div>
            <button type="button" class="submit-btn" id="submitBtn" onclick="submitSign()">确认签收</button>
        </form>
    </div>

    <!-- 全屏签名弹窗 -->
    <div class="sign-modal" id="signModal">
        <div class="sign-modal-header">
            <span class="modal-title">手写签名</span>
            <button class="modal-close" onclick="closeSignModal()">取消</button>
        </div>
        <div class="sign-modal-body">
            <canvas id="signCanvas"></canvas>
        </div>
        <div class="sign-modal-footer">
            <button class="modal-btn btn-clear" onclick="clearCanvas()">清除</button>
            <button class="modal-btn btn-confirm" onclick="confirmSign()">确认</button>
        </div>
    </div>

    <!-- 提示消息 -->
    <div class="toast" id="toast"></div>

    <script>
        var canvas = document.getElementById('signCanvas');
        var ctx = canvas.getContext('2d');
        var drawing = false;
        var signatureData = null;

        function initCanvas() {
            var rect = canvas.getBoundingClientRect();
            canvas.width = rect.width;
            canvas.height = rect.height;
            ctx.strokeStyle = '#333';
            ctx.lineWidth = 3;
            ctx.lineCap = 'round';
        }

        function openSignModal() {
            document.getElementById('signModal').classList.add('active');
            document.body.style.overflow = 'hidden';
            setTimeout(initCanvas, 100);
        }

        function closeSignModal() {
            document.getElementById('signModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        // 鼠标事件
        canvas.addEventListener('mousedown', function(e) {
            drawing = true;
            var pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        });
        canvas.addEventListener('mousemove', function(e) {
            if (!drawing) return;
            var pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        });
        canvas.addEventListener('mouseup', function() { drawing = false; });
        canvas.addEventListener('mouseleave', function() { drawing = false; });

        // 触摸事件
        canvas.addEventListener('touchstart', function(e) { e.preventDefault(); drawing = true; var pos = getPos(e.touches[0]); ctx.beginPath(); ctx.moveTo(pos.x, pos.y); });
        canvas.addEventListener('touchmove', function(e) { e.preventDefault(); if (!drawing) return; var pos = getPos(e.touches[0]); ctx.lineTo(pos.x, pos.y); ctx.stroke(); });
        canvas.addEventListener('touchend', function() { drawing = false; });

        function getPos(e) {
            var rect = canvas.getBoundingClientRect();
            var scaleX = canvas.width / rect.width;
            var scaleY = canvas.height / rect.height;
            return {
                x: (e.clientX - rect.left) * scaleX,
                y: (e.clientY - rect.top) * scaleY
            };
        }

        function clearCanvas() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }

        function confirmSign() {
            signatureData = canvas.toDataURL('image/png');

            // 更新预览
            document.getElementById('signPreviewImg').src = signatureData;
            document.getElementById('signPreviewImg').style.display = 'block';
            document.getElementById('signPlaceholder').style.display = 'none';
            document.getElementById('signActions').style.display = 'block';

            closeSignModal();
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
            if (!signatureData) {
                showToast('请手写签名', 'error');
                return;
            }

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
                signature: signatureData
            }));
        }
    </script>
</body>
</html>
