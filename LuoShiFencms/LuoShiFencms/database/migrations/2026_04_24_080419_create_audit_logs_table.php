<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * audit_logs 表 —— 操作审计日志
 *
 * 历史说明：本文件原先只有 id + timestamps（空壳迁移）。此处按真实结构补齐。
 *
 * 注意：本表只有 created_at，没有 updated_at —— App\AuditLog 已设
 * $timestamps = false，写入时需自行带上 created_at。
 */
class CreateAuditLogsTable extends Migration
{
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name', 100)->nullable();
            $table->string('action', 100);
            $table->text('description')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
}
