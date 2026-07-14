<?php

namespace App\Console\Commands;

use App\DispatchRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ArchiveDispatchRecords extends Command
{
    protected $signature = 'dispatch:archive';
    protected $description = '将超过3年的发货记录迁移到归档存储';

    public function handle()
    {
        $threeYearsAgo = now()->subYears(3);

        // 查询3年前的发货记录
        $records = DispatchRecord::where('created_at', '<', $threeYearsAgo)->get();

        foreach ($records as $record) {
            // 迁移照片
            if ($record->photo_path && Storage::disk('hot')->exists($record->photo_path)) {
                $content = Storage::disk('hot')->get($record->photo_path);
                Storage::disk('archive')->put($record->photo_path, $content);
            }

            // 迁移签收签名
            if ($record->signRecord && $record->signRecord->signature_path) {
                $path = $record->signRecord->signature_path;
                if (Storage::disk('hot')->exists($path)) {
                    $content = Storage::disk('hot')->get($path);
                    Storage::disk('archive')->put($path, $content);
                }
            }

            // 更新存储标记（可选）
            $record->storage_tier = 'archive';
            $record->save();
        }

        $this->info("已归档 {$records->count()} 条记录");
    }
}