<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Enterprise extends Model
{
    protected $fillable = ['name', 'contact', 'phone', 'status'];

    public function users()
    {
        return $this->hasMany(User::class, 'enterprise_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (Enterprise $enterprise) {
            DispatchRecord::where('enterprise_id', $enterprise->id)->delete();
            $enterprise->users()->delete();
            Department::where('enterprise_id', $enterprise->id)->delete();
            ReceivingUnit::where('enterprise_id', $enterprise->id)->delete();
        });
    }
}