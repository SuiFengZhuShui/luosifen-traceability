<?php

namespace App\Http\Controllers\Api;

use App\DispatchRecord;
use App\SignRecord;
use App\AuditLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SignController extends Controller
{
    /**
     * 显示签收页面（免登录）
     */
    public function show($id, Request $request)
    {
        $signData = $this->getSignData($id);

        // 小程序请求（带 Accept: application/json），返回 JSON
        if ($request->expectsJson()) {
            return response()->json([
                'code' => 200,
                'data' => [
                    'already_signed' => $signData['already_signed'],
                    'dispatch' => [
                        'id' => $signData['dispatch']->id,
                        'sales_order_no' => $signData['dispatch']->sales_order_no,
                        'product_name' => $signData['dispatch']->product_name,
                        'spec' => $signData['dispatch']->spec,
                        'quantity' => $signData['dispatch']->quantity,
                        'batch_no' => $signData['dispatch']->batch_no,
                        'status' => $signData['dispatch']->status,
                        'receiving_unit_name' => $signData['dispatch']->receivingUnit->name ?? '',
                        'buyer_name' => $signData['dispatch']->buyer_name,
                    ],
                    'sign_info' => $signData['sign_info'] ?? null
                ]
            ]);
        }

        // 浏览器访问，返回 HTML 视图
        if ($signData['already_signed']) {
            return view('sign.already_signed', [
                'dispatch' => $signData['dispatch'],
                'signRecord' => $signData['sign_info'] ?? null
            ]);
        }

        return view('sign.form', ['dispatch' => $signData['dispatch']]);
    }

    /**
     * 提交签收（免登录）
     */
    public function store(Request $request, $id)
    {
        $dispatch = DispatchRecord::findOrFail($id);

        // 防止重复签收
        if ($dispatch->status === 'signed') {
            return response()->json([
                'code' => 400,
                'message' => '该记录已完成签收，无法重复签收'
            ], 400);
        }

        $request->validate([
            'actual_quantity' => 'required|integer|min:1',
            'receiver_name' => 'required|string|max:50',
            'receiver_phone' => 'nullable|string|max:20',
            'signature' => 'required|string',
        ]);

        // 处理手写签名图片
        $signatureData = $request->signature;
        $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureData));
        $signaturePath = 'signatures/' . $id . '_' . time() . '.png';
        Storage::disk('public')->put($signaturePath, $image);

        // 生成随机盐值
        $nonce = Str::random(32);

        // 创建签收记录（含数据哈希）
        $signRecord = SignRecord::create([
            'dispatch_record_id' => $id,
            'actual_quantity' => $request->actual_quantity,
            'receiver_name' => $request->receiver_name,
            'receiver_phone' => $request->receiver_phone,
            'signature_path' => $signaturePath,
            'signed_at' => now(),
        ]);

        // 写入审计日志
        AuditLog::create([
            'user_id' => null,
            'user_name' => $request->receiver_name,
            'action' => '签收确认',
            'description' => "发货记录ID: {$id}",
            'ip' => $request->ip(),
            'created_at' => now(),
        ]);

        // 更新发货状态
        $dispatch->status = 'signed';
        $dispatch->save();

        return response()->json([
            'code'    => 200,
            'message' => '签收成功',
            'data'    => [
                'dispatch_id' => $dispatch->id,
                'signature_url' => Storage::url($signaturePath),
            ]
        ]);
    }

    /**
     * 获取签收数据
     */
    protected function getSignData($id)
    {
        $dispatch = DispatchRecord::with('receivingUnit')->findOrFail($id);
        $signRecord = SignRecord::where('dispatch_record_id', $id)->first();

        if ($dispatch->status === 'signed') {
            return [
                'already_signed' => true,
                'dispatch' => $dispatch,
                'sign_info' => $signRecord
            ];
        }

        return [
            'already_signed' => false,
            'dispatch' => $dispatch
        ];
    }
}