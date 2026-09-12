<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * enterprises 表 —— 生产企业
 *
 * 历史说明：本文件原先只有 id + timestamps，业务字段缺失（空壳迁移），
 * 与实际使用的表结构不符。此处按真实结构补齐。
 */
class CreateEnterprisesTable extends Migration
{
    public function up()
    {
        Schema::create('enterprises', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100);
            $table->string('contact', 50)->nullable()->comment('联系人');
            $table->string('phone', 20)->nullable();
            $table->tinyInteger('status')->default(1)->comment('1启用 0禁用');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('enterprises');
    }
}
