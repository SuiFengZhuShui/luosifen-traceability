<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = ['user_id', 'user_name', 'action', 'description', 'ip'];

    public $timestamps = false;
}