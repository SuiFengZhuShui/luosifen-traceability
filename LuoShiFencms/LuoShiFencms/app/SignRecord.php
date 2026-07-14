<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SignRecord extends Model
{
    protected $fillable = [
        'dispatch_record_id', 'actual_quantity', 'receiver_name',
        'receiver_phone', 'signature_path', 'signed_at'
    ];

    protected $dates = ['signed_at'];

    public function dispatchRecord()
    {
        return $this->belongsTo(DispatchRecord::class);
    }
}