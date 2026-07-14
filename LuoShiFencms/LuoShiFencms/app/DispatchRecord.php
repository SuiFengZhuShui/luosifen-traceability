<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DispatchRecord extends Model
{
    protected $fillable = [
        'enterprise_id', 'user_id', 'department_id',
        'sales_order_no', 'buyer_name', 'product_name', 'spec',
        'quantity', 'batch_no', 'production_date', 'receiving_unit_id',
        'status', 'photo_path', 'qrcode_path'
    ];

    public function signRecord()
    {
        return $this->hasOne(SignRecord::class);
    }

    public function dispatcher()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function receivingUnit()
    {
        return $this->belongsTo(ReceivingUnit::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}