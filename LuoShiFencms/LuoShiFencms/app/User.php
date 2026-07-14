<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
    'account',
    'name',
    'email',
    'phone',       
    'password',
    'role',
    'enterprise_id',
    'department_id',
    'receiving_unit_id',
    'status',
];

    protected $hidden = ['password', 'remember_token'];

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function isCompanyAdmin()
    {
        return $this->role === 'company_admin';
    }

    public function isDispatcher()
    {
        return $this->role === 'dispatcher';
    }
}