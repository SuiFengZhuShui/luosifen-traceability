<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * departments 表 —— 企业内部部门
 *
 * 历史说明：本文件原先只有 id + timestamps（空壳迁移）。此处按真实结构补齐。
 */
class CreateDepartmentsTable extends Migration
{
    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('enterprise_id');
            $table->string('name', 100);
            $table->timestamps();

            $table->index('enterprise_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('departments');
    }
}
