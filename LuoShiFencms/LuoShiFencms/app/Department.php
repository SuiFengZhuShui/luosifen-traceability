<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['enterprise_id', 'name'];

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function employees()
    {
        return $this->hasMany(User::class, 'department_id');
    }
}