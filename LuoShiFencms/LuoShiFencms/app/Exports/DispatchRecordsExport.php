<?php

namespace App\Exports;

use App\DispatchRecord;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DispatchRecordsExport implements FromQuery, WithHeadings, WithMapping
{
    protected $enterpriseId;

    public function __construct($enterpriseId)
    {
        $this->enterpriseId = $enterpriseId;
    }

    public function query()
    {
        return DispatchRecord::where('enterprise_id', $this->enterpriseId)
                ->with(['dispatcher', 'department', 'receivingUnit', 'signRecord']);
    }

    public function headings(): array
    {
        return [
            '销售单号',
            '购买方名称',
            '产品名称',
            '规格',
            '数量',
            '批次号',
            '生产日期',
            '发货员',
            '部门',
            '收货单位',
            '状态',
            '签收人',
            '实收数量',
            '签收时间',
        ];
    }

    public function map($record): array
    {
        return [
            $record->sales_order_no,
            $record->buyer_name,
            $record->product_name,
            $record->spec,
            $record->quantity,
            $record->batch_no,
            $record->production_date,
            $record->dispatcher->name ?? '',
            $record->department->name ?? '',
            $record->receivingUnit->name ?? '',
            $record->status == 'signed' ? '已签收' : '待签收',
            $record->signRecord->receiver_name ?? '',
            $record->signRecord->actual_quantity ?? '',
            $record->signRecord->signed_at ?? '',
        ];
    }
}