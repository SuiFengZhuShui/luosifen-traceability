<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * dispatch_records 表 —— 发货单（一张发货单对应一个溯源码）
 *
 * 历史说明：本文件原先只有 id + timestamps（空壳迁移）。此处按真实结构补齐。
 *
 * 索引说明：原库在 batch_no 上重复建了两个索引、在
 * (enterprise_id, receiving_unit_id, status) 上重复建了两个复合索引，
 * 属冗余。此处各保留一个，查询能力不变。
 */
class CreateDispatchRecordsTable extends Migration
{
    public function up()
    {
        Schema::create('dispatch_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('enterprise_id');
            $table->unsignedBigInteger('user_id')->comment('发货员');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('sales_order_no', 100)->nullable()->comment('销售单号');
            $table->string('buyer_name', 100)->nullable()->comment('购买方名称');
            $table->string('product_name', 100)->nullable();
            $table->string('spec', 50)->nullable();
            $table->integer('quantity')->default(0);
            $table->string('batch_no', 100)->comment('产品批次号');
            $table->date('production_date')->nullable();
            $table->unsignedBigInteger('receiving_unit_id')->nullable();
            $table->enum('status', ['pending', 'signed'])->default('pending');
            $table->string('photo_path')->nullable()->comment('销售单照片');
            $table->string('qrcode_path')->nullable();
            $table->timestamps();

            $table->index('batch_no');
            $table->index(['enterprise_id', 'receiving_unit_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('dispatch_records');
    }
}
