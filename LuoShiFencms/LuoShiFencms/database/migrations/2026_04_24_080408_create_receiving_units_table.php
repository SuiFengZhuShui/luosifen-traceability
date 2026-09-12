<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * receiving_units 表 —— 收货单位（下游经销商 / 门店）
 *
 * 历史说明：本文件原先只有 id + timestamps（空壳迁移）。此处按真实结构补齐。
 */
class CreateReceivingUnitsTable extends Migration
{
    public function up()
    {
        Schema::create('receiving_units', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('enterprise_id');
            $table->string('name', 100);
            $table->string('contact_person', 50)->nullable()->comment('联系人');
            $table->string('phone', 20)->nullable();
            $table->string('address', 200)->nullable();
            $table->string('account_name', 50)->nullable()->comment('登录用户名');
            $table->string('password')->nullable()->comment('bcrypt密码');
            $table->tinyInteger('status')->default(1)->comment('1启用 0禁用');
            $table->timestamps();

            $table->index('enterprise_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('receiving_units');
    }
}
