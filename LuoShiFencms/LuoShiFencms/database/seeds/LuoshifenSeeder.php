<?php

use App\AuditLog;
use App\Department;
use App\DispatchRecord;
use App\Enterprise;
use App\ReceivingUnit;
use App\SignRecord;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * 螺蛳粉溯源系统 —— 演示数据
 *
 * 造一套可直接演示的完整数据：4 家企业、5 个部门、6 个收货单位、
 * 8 个账号（1 超管 / 3 企业管理员 / 4 发货员）、9 张发货单（6 待签 + 3 已签）、
 * 3 条签收记录（含实收数量短少的真实场景）与若干审计日志。
 *
 * 演示账号统一密码取自 .env 的 SEED_PASSWORD（不硬编码在代码里）。
 * 生产环境请务必改掉 .env.example 中的默认值。
 *
 * 使用：
 *   php artisan migrate:fresh --seed
 *   php artisan qrcode:regenerate      # 生成发货单二维码图片
 */
class LuoshifenSeeder extends Seeder
{
    /**
     * 演示账号统一密码（来自 .env 的 SEED_PASSWORD）
     *
     * @var string
     */
    private $password;

    public function run()
    {
        $this->password = $this->resolvePassword();

        $enterprises = $this->seedEnterprises();
        $departments = $this->seedDepartments($enterprises);
        $units       = $this->seedReceivingUnits($enterprises);
        $users       = $this->seedUsers($enterprises, $departments);

        $dispatches = $this->seedDispatchRecords($enterprises, $departments, $units, $users);

        $this->seedSignRecords($dispatches);
        $this->seedAuditLogs($users);
        $this->printSummary();
    }

    /**
     * 读取并校验 SEED_PASSWORD
     */
    private function resolvePassword()
    {
        $password = env('SEED_PASSWORD');

        if (empty($password)) {
            throw new RuntimeException(
                '未配置 SEED_PASSWORD —— 请在 .env 中添加 SEED_PASSWORD=你的演示密码 后再执行 seeder。'
            );
        }

        return $password;
    }

    /**
     * 4 家生产企业
     */
    private function seedEnterprises()
    {
        $rows = [
            '柳州中柳食品有限公司'   => ['王经理', '0772-1234567'],
            '柳州沪桂食品有限公司'   => ['李经理', '0772-7654321'],
            '柳州螺霸王食品有限公司' => ['张经理', '0772-8889999'],
            '柳州雷霆螺蛳粉有限公司' => ['蓝经理', '0772-5551236'],
        ];

        $result = [];
        foreach ($rows as $name => $meta) {
            $values = ['contact' => $meta[0], 'phone' => $meta[1], 'status' => 1];
            $result[$name] = Enterprise::firstOrCreate(['name' => $name], $values);
        }

        return $result;
    }

    /**
     * 中柳食品的 5 个部门
     */
    private function seedDepartments(array $enterprises)
    {
        $enterprise = $enterprises['柳州中柳食品有限公司'];
        $names = ['生产部', '仓储部', '发货一组', '发货二组', '品控部'];

        $result = [];
        foreach ($names as $name) {
            $key = ['enterprise_id' => $enterprise->id, 'name' => $name];
            $result[$name] = Department::firstOrCreate($key);
        }

        return $result;
    }

    /**
     * 中柳食品的 6 个收货单位（下游经销商 / 门店）
     *
     * 每行：[名称, 联系人, 电话, 地址, 登录用户名]
     */
    private function seedReceivingUnits(array $enterprises)
    {
        $enterprise = $enterprises['柳州中柳食品有限公司'];

        $rows = [
            ['北京朝阳经销商', '赵老板', '010-11112222', '北京市朝阳区XX路100号', 'beijing'],
            ['上海浦东超市', '钱老板', '021-33334444', '上海市浦东新区XX商场', 'shanghai'],
            ['广州天河批发商', '孙老板', '020-55556666', '广州市天河区XX批发市场', 'guangzhou'],
            ['深圳南山代理', '周老板', '0755-77778888', '深圳市南山区XX大厦', 'shenzhen'],
            ['成都武侯门店', '吴老板', '028-99990000', '成都市武侯区XX商业街', 'chengdu'],
            ['纽约时代广场', '杰克', '020-55556666', '纽约时代广场XX商城', 'niuyue'],
        ];

        $result = [];
        foreach ($rows as $row) {
            $key = ['enterprise_id' => $enterprise->id, 'name' => $row[0]];
            $values = $this->receivingUnitValues($row);
            $result[$row[0]] = ReceivingUnit::firstOrCreate($key, $values);
        }

        return $result;
    }

    /**
     * 收货单位的写入字段
     */
    private function receivingUnitValues(array $row)
    {
        return [
            'contact_person' => $row[1],
            'phone'          => $row[2],
            'address'        => $row[3],
            'account_name'   => $row[4],
            'password'       => Hash::make($this->password),
            'status'         => 1,
        ];
    }

    /**
     * 8 个账号：1 超管 + 3 企业管理员 + 4 发货员
     *
     * 每行：[账号, 姓名, 角色, 所属企业, 所属部门, 手机号]
     */
    private function seedUsers(array $enterprises, array $departments)
    {
        $rows = [
            ['admin', '超级管理员', 'super_admin', null, null, '13800000000'],
            ['zhongliuadmin', '中柳管理员', 'company_admin', '柳州中柳食品有限公司', null, '13900001111'],
            ['huguiadmin', '沪桂管理员', 'company_admin', '柳州沪桂食品有限公司', null, '13900002222'],
            ['leitingadmin', '雷霆管理员', 'company_admin', '柳州雷霆螺蛳粉有限公司', null, '13900006666'],
            ['zhangsan', '发货员张三', 'dispatcher', '柳州中柳食品有限公司', '发货一组', '13800003333'],
            ['lisi', '发货员李四', 'dispatcher', '柳州中柳食品有限公司', '发货二组', '13800004444'],
            ['wangwu', '发货员王五', 'dispatcher', '柳州中柳食品有限公司', '发货一组', '13800005555'],
            ['zhaoliu', '发货员赵六', 'dispatcher', '柳州中柳食品有限公司', '发货二组', '13800006666'],
        ];

        $result = [];
        foreach ($rows as $row) {
            $account = $row[0];
            $values = $this->userValues($row, $enterprises, $departments);
            $result[$account] = User::firstOrCreate(['account' => $account], $values);
        }

        return $result;
    }

    /**
     * 用户的写入字段
     */
    private function userValues(array $row, array $enterprises, array $departments)
    {
        $enterpriseName = $row[3];
        $departmentName = $row[4];

        return [
            'name'          => $row[1],
            'email'         => $row[0] . '@test.com',
            'phone'         => $row[5],
            'password'      => Hash::make($this->password),
            'role'          => $row[2],
            'enterprise_id' => $enterpriseName ? $enterprises[$enterpriseName]->id : null,
            'department_id' => $departmentName ? $departments[$departmentName]->id : null,
            'status'        => 1,
        ];
    }

    /**
     * 9 张发货单：6 张待签收、3 张已签收
     *
     * 二维码图片不在此处生成（qrcode_path 留空），
     * 建库后执行 php artisan qrcode:regenerate 按 APP_URL 批量生成。
     */
    private function seedDispatchRecords(array $enterprises, array $departments, array $units, array $users)
    {
        $enterprise = $enterprises['柳州中柳食品有限公司'];
        $rows = $this->dispatchRows();

        $result = [];
        foreach ($rows as $row) {
            $orderNo = $row[0];
            $values = $this->dispatchValues($row, $enterprise, $departments, $units, $users);
            $result[$orderNo] = DispatchRecord::firstOrCreate(['sales_order_no' => $orderNo], $values);
        }

        return $result;
    }

    /**
     * 发货单演示数据
     *
     * 每行：[销售单号, 购买方, 产品, 规格, 数量, 批次号, 生产日期,
     *        收货单位, 发货员, 发货部门, 状态]
     */
    private function dispatchRows()
    {
        return [
            ['SO20260422001', '北京朝阳经销商', '柳州螺蛳粉经典原味', '300g/袋', 200, 'LSF20260422001', '2026-04-20', '北京朝阳经销商', 'zhangsan', '发货一组', 'pending'],
            ['SO20260422002', '上海浦东超市', '柳州螺蛳粉麻辣味', '300g/袋', 150, 'LSF20260422002', '2026-04-20', '上海浦东超市', 'zhangsan', '发货一组', 'pending'],
            ['SO20260423001', '广州天河批发商', '柳州螺蛳粉酸辣味', '280g/袋', 300, 'LSF20260423001', '2026-04-21', '广州天河批发商', 'lisi', '发货二组', 'pending'],
            ['SO20260423002', '深圳南山代理', '柳州螺蛳粉原味豪华装', '350g/盒', 100, 'LSF20260423002', '2026-04-21', '深圳南山代理', 'lisi', '发货二组', 'pending'],
            ['SO20260424001', '成都武侯门店', '柳州螺蛳粉藤椒味', '300g/袋', 250, 'LSF20260424001', '2026-04-22', '成都武侯门店', 'wangwu', '发货一组', 'pending'],
            ['SO20260420001', '北京朝阳经销商', '柳州螺蛳粉经典原味', '300g/袋', 180, 'LSF20260420001', '2026-04-18', '北京朝阳经销商', 'zhangsan', '发货一组', 'signed'],
            ['SO20260421001', '上海浦东超市', '柳州螺蛳粉麻辣味', '300g/袋', 120, 'LSF20260421001', '2026-04-19', '上海浦东超市', 'lisi', '发货二组', 'signed'],
            ['SO20260421002', '广州天河批发商', '柳州螺蛳粉酸辣味', '280g/袋', 280, 'LSF20260421002', '2026-04-19', '广州天河批发商', 'wangwu', '发货一组', 'signed'],
            ['SO20260503001', '北京朝阳经销商', '柳州螺蛳粉经典原味', '300g/袋', 200, 'LSF20260503001', '2026-05-03', '北京朝阳经销商', 'zhangsan', '发货一组', 'pending'],
        ];
    }

    /**
     * 发货单的写入字段
     */
    private function dispatchValues(array $row, $enterprise, array $departments, array $units, array $users)
    {
        return [
            'enterprise_id'     => $enterprise->id,
            'user_id'           => $users[$row[8]]->id,
            'department_id'     => $departments[$row[9]]->id,
            'buyer_name'        => $row[1],
            'product_name'      => $row[2],
            'spec'              => $row[3],
            'quantity'          => $row[4],
            'batch_no'          => $row[5],
            'production_date'   => $row[6],
            'receiving_unit_id' => $units[$row[7]]->id,
            'status'            => $row[10],
            'qrcode_path'       => null,
        ];
    }

    /**
     * 3 条签收记录 —— 对应 3 张已签收的发货单
     *
     * 刻意保留真实场景：实收数量少于发货数量的短少情况
     * （SO20260421001 发 120 收 118、SO20260421002 发 280 收 278）。
     */
    private function seedSignRecords(array $dispatches)
    {
        $rows = [
            ['SO20260420001', 180, '赵老板', '13911112222'],
            ['SO20260421001', 118, '钱老板', '13922223333'],
            ['SO20260421002', 278, '孙老板', '13933334444'],
        ];

        foreach ($rows as $row) {
            $key = ['dispatch_record_id' => $dispatches[$row[0]]->id];
            $values = $this->signValues($row);
            SignRecord::firstOrCreate($key, $values);
        }
    }

    /**
     * 签收记录的写入字段
     */
    private function signValues(array $row)
    {
        return [
            'actual_quantity' => $row[1],
            'receiver_name'   => $row[2],
            'receiver_phone'  => $row[3],
        ];
    }

    /**
     * 若干审计日志（App\AuditLog 的 $timestamps = false，需自行带 created_at）
     */
    private function seedAuditLogs(array $users)
    {
        $rows = [
            ['admin', 'login', '超级管理员登录系统'],
            ['zhongliuadmin', 'create', '新增收货单位：纽约时代广场'],
            ['zhongliuadmin', 'update', '修改企业资料'],
            ['zhangsan', 'dispatch', '创建发货单 SO20260422001'],
            ['zhangsan', 'dispatch', '创建发货单 SO20260422002'],
            ['lisi', 'dispatch', '创建发货单 SO20260423001'],
            ['lisi', 'dispatch', '创建发货单 SO20260423002'],
            ['wangwu', 'dispatch', '创建发货单 SO20260424001'],
            ['zhangsan', 'export', '导出发货记录'],
        ];

        foreach ($rows as $index => $row) {
            $values = $this->auditValues($users[$row[0]], $row, $index);
            AuditLog::create($values);
        }
    }

    /**
     * 审计日志的写入字段
     */
    private function auditValues($user, array $row, $index)
    {
        return [
            'user_id'     => $user->id,
            'user_name'   => $user->name,
            'action'      => $row[1],
            'description' => $row[2],
            'ip'          => '127.0.0.1',
            'created_at'  => now()->subMinutes(60 - $index * 5),
        ];
    }

    /**
     * 打印演示账号，方便直接登录
     */
    private function printSummary()
    {
        $this->command->info('演示数据已写入，密码取自 .env 的 SEED_PASSWORD。');
        $this->command->info('  超级管理员  admin         → /super-admin/dashboard');
        $this->command->info('  企业管理员  zhongliuadmin → /company-admin/dashboard');
        $this->command->info('  发货员      zhangsan      → /mobile/home');
        $this->command->info('提示：执行 php artisan qrcode:regenerate 生成发货单二维码图片。');
    }
}
