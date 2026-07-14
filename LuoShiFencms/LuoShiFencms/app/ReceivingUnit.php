<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReceivingUnit extends Model
{
    protected $fillable = [
        'enterprise_id', 'name', 'contact_person', 'phone',
        'address', 'account_name', 'password', 'status'
    ];

    protected $hidden = ['password'];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }
}