<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\DispatchRecord;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class RegenerateQrcodes extends Command
{
    protected $signature = 'qrcode:regenerate';
    protected $description = '重新生成所有发货记录的二维码';

    public function handle()
    {
        $records = DispatchRecord::all();
        $count = 0;

        foreach ($records as $record) {
            // 生成新的二维码（内容为签名链接）
            $url = url('/sign/' . $record->id);
            $qrImg = QrCode::format('png')->size(300)->generate($url);
            $qrcodePath = 'qrcodes/' . $record->id . '.png';
            Storage::disk('public')->put($qrcodePath, $qrImg);

            // 更新数据库路径
            $record->qrcode_path = $qrcodePath;
            $record->save();

            $this->info("已重新生成: ID={$record->id}, URL={$url}");
            $count++;
        }

        $this->info("共重新生成 {$count} 个二维码");
    }
}
