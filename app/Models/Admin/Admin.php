<?php

namespace App\Models\Admin;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Admin extends Authenticatable implements JWTSubject
{
    use Notifiable;

    const USER_STATUS_DELETED = -1;
    const USER_STATUS_NORMAL  = 0;
    const USER_STATUS_FREEZE  = 1;


    public static $userStatusMap = [
        self::USER_STATUS_DELETED => '已删除',
        self::USER_STATUS_NORMAL  => '正常',
        self::USER_STATUS_FREEZE  => '冻结'
    ];


    protected $fillable = [
        'name', 'password',
    ];


    protected $hidden = [
        'password'
    ];


    //管理员有哪些角色
    public function roles()
    {
        return $this->belongsToMany(AdminRole::class, 'admin_role_user', 'admin_id', 'role_id')
                    ->withPivot(['admin_id', 'role_id']);
    }

    //是否有某个觉得、某些角色
    public function isInRoles($roles)
    {
        return !!$roles->intersect($this->roles)->count();
    }

    //给管理员分配角色
    public function assignRole($role)
    {
        return $this->roles()->save($role);
    }

    //取消管理员分配的角色
    public function deleteRole($role)
    {
        return $this->roles()->detach($role);
    }

    //管理员是否有权限

    public function hasPermission($permission)
    {
        return $this->isInRoles($permission->roles);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }
}
