<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * users 表 —— 平台账号表
 *
 * 历史说明：本文件原先保留 Laravel 原版建表语句（只有 name/email/password），
 * 与实际使用的表结构不符 —— App\User 模型与 Auth\LoginController 依赖
 * account / role / enterprise_id / department_id / receiving_unit_id / status
 * 六个字段，其中 account 与 role 为 NOT NULL。此处按真实结构重建。
 *
 * 注意：已跑过旧迁移的数据库不会重新执行本文件；如需修正存量库，
 * 请另写一个 Schema::table('users', ...) 的补丁迁移。
 */
class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('account', 100)->unique()->comment('登录账号');
            $table->string('name', 100);
            $table->string('email', 100)->nullable()->unique();
            $table->string('phone', 20)->nullable();
            $table->string('password')->comment('bcrypt');
            $table->enum('role', ['super_admin', 'company_admin', 'dispatcher', 'receiver_admin']);
            $table->unsignedBigInteger('enterprise_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable()->comment('仅发货员');
            $table->unsignedBigInteger('receiving_unit_id')->nullable()->comment('收货单位管理员关联');
            $table->tinyInteger('status')->default(1)->comment('1启用 0禁用');
            $table->rememberToken();
            $table->timestamps();

            $table->index('enterprise_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
