<?php

namespace App\Http\Controllers\Api;

use App\DispatchRecord;
use App\ReceivingUnit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class DispatchController extends Controller
{
    // OCR 识别
    public function ocr(Request $request)
    {
        $imageBase64 = $request->input('image');

        if (!$imageBase64) {
            return response()->json(['error' => '缺少图片数据'], 422);
        }

        // 调用百度 OCR
        $result = $this->baiduOcr($imageBase64);

        if (isset($result['error'])) {
            return response()->json([
                'sales_order_no' => 'SO' . now()->format('YmdHis'),
                'buyer_name'     => '演示客户',
                'product_name'   => '柳州螺蛳粉',
                'spec'           => '300g/袋',
                'quantity'       => 100,
            ]);
        }

        // 提取文字
        $lines = $this->parseOcrResult($result);

        return response()->json([
            'sales_order_no' => $lines[0] ?? '',
            'buyer_name'     => $lines[1] ?? '',
            'product_name'   => $lines[2] ?? '',
            'spec'           => $lines[3] ?? '',
            'quantity'       => intval($lines[4] ?? 0),
        ]);
    }

    /**
     * 调用百度 OCR API
     */
    private function baiduOcr($imageBase64)
    {
        $appId     = env('BAIDU_OCR_APP_ID');
        $apiKey    = env('BAIDU_OCR_API_KEY');
        $secretKey = env('BAIDU_OCR_SECRET_KEY');

        if (!$appId || !$apiKey || !$secretKey) {
            return ['error' => '百度 OCR 未配置'];
        }

        // 获取 access_token
        $tokenUrl = "https://aip.baidubce.com/oauth/2.0/token?grant_type=client_credentials&client_id={$apiKey}&client_secret={$secretKey}";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tokenUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
        $tokenRes = curl_exec($ch);
        curl_close($ch);
        $tokenData = json_decode($tokenRes, true);
        
        if (!isset($tokenData['access_token'])) {
            return ['error' => '获取 token 失败'];
        }

        $accessToken = $tokenData['access_token'];

        // 调用通用文字识别
        $ocrUrl = "https://aip.baidubce.com/rest/2.0/ocr/v1/general_basic?access_token={$accessToken}";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ocrUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'image' => base64_encode(base64_decode($imageBase64)),
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    /**
     * 解析 OCR 结果
     */
    private function parseOcrResult($result)
    {
        $text = '';
        if (isset($result['words_result'])) {
            foreach ($result['words_result'] as $word) {
                $text .= $word['words'] . "\n";
            }
        }
        return array_values(array_filter(explode("\n", $text)));
    }


    // 获取本企业收货单位列表
    public function receivingUnits()
    {
        $enterpriseId = auth()->user()->enterprise_id;
        $units = ReceivingUnit::where('enterprise_id', $enterpriseId)
                    ->where('status', 1)
                    ->select('id', 'name')
                    ->get();
        return response()->json($units);
    }

    // 提交发货记录
    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'sales_order_no' => 'nullable|string|max:100',
            'buyer_name' => 'nullable|string|max:100',
            'product_name' => 'nullable|string|max:100',
            'spec' => 'nullable|string|max:50',
            'quantity' => 'required|integer|min:1',
            'batch_no' => 'required|string|max:100',
            'production_date' => 'nullable|date',
            'receiving_unit_id' => 'required|exists:receiving_units,id',
            'photo' => 'nullable|image|max:2048',
        ]);

        $record = new DispatchRecord();
        $record->fill($request->only([
            'sales_order_no', 'buyer_name', 'product_name', 'spec',
            'quantity', 'batch_no', 'production_date'
        ]));
        $record->receiving_unit_id = $request->receiving_unit_id;
        $record->enterprise_id = $user->enterprise_id;
        $record->user_id = $user->id;
        $record->department_id = $user->department_id;
        $record->status = 'pending';

        if ($request->hasFile('photo')) {
            $record->photo_path = $request->file('photo')->store('dispatch_photos', 'public');
        }

        $record->save();

        // 生成二维码
        $url = url('/sign/' . $record->id);
        $qrImg = QrCode::format('png')->size(300)->generate($url);
        $qrcodePath = 'qrcodes/' . $record->id . '.png';
        Storage::disk('public')->put($qrcodePath, $qrImg);
        $record->qrcode_path = $qrcodePath;
        $record->save();

        // 返回完整的二维码 URL
        $qrcodeUrl = rtrim(env('APP_URL', 'http://127.0.0.1'), '/') . '/storage/' . $qrcodePath;

        return response()->json([
            'success'     => true,
            'dispatch_id' => $record->id,
            'qrcode_url'  => $qrcodeUrl,
        ]);
    }

    // 发货员查看自己记录
    public function index()
    {
        $records = DispatchRecord::where('user_id', auth()->id())
                    ->with('receivingUnit')
                    ->latest()
                    ->get();

        $data = [];
        foreach ($records as $record) {
            $data[] = [
                'id'              => $record->id,
                'product_name'    => $record->product_name ?: '未命名产品',
                'batch_no'        => $record->batch_no,
                'quantity'        => (int) $record->quantity,
                'status'          => $record->status,
                'buyer_name'      => $record->buyer_name ?: '',
                'sales_order_no'  => $record->sales_order_no ?: '',
                'spec'            => $record->spec ?: '',
                'created_at'      => optional($record->created_at)->toDateTimeString() ?: '',
                'receiving_unit'  => optional($record->receivingUnit)->name ?: '',
                // 返回完整可访问的二维码 URL
                'qrcode_url'      => $this->getQrcodeUrl($record),
            ];
        }

        return response()->json($data);
    }

    /**
     * 获取二维码完整 URL
     */
    private function getQrcodeUrl($record)
    {
        if (!$record->qrcode_path) {
            return null;
        }
        
        // 拼接完整 URL（上线后改为正式域名）
        return rtrim(env('APP_URL', 'http://127.0.0.1'), '/') . '/storage/' . $record->qrcode_path;
    }
}