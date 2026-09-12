<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * sign_records 表 —— 签收记录
 *
 * 历史说明：本文件原先只有 id + timestamps（空壳迁移）。此处按真实结构补齐。
 *
 * 字段说明：
 * - dispatch_record_id 唯一索引 —— 一张发货单只能签收一次
 * - actual_quantity —— 实收数量，可与发货数量不等（短少/溢装留痕）
 * - signature_path —— 手写签名图片路径
 * - data_hash / hash_created_at —— 预留字段（签收数据 SHA256 防篡改哈希）。
 *   注意：当前业务代码并未写入这两个字段，全库均为 NULL，属尚未启用的设计
 * - signed_at 在原库为 ON UPDATE CURRENT_TIMESTAMP，此处保持一致，
 *   注意该行为会让本行任何更新都刷新 signed_at
 */
class CreateSignRecordsTable extends Migration
{
    public function up()
    {
        Schema::create('sign_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dispatch_record_id');
            $table->integer('actual_quantity')->nullable()->comment('实收数量');
            $table->string('receiver_name', 50)->nullable();
            $table->string('receiver_phone', 20)->nullable();
            $table->string('signature_path')->nullable()->comment('手写签名图片');
            $table->string('data_hash', 64)->nullable()->comment('数据SHA256哈希');
            $table->timestamp('hash_created_at')->nullable()->comment('哈希生成时间');
            $table->timestamp('signed_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamps();

            $table->unique('dispatch_record_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sign_records');
    }
}
