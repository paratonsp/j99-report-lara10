<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'v2_users';
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'role_uuid',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function scopeGetUserList($query)
    {
        $query = DB::table("v2_users")
            ->select('v2_users.uuid', 'v2_users.name', 'v2_users.email', 'v2_users.role_uuid', 'role.title as rolename')
            ->join("v2_role AS role", "role.uuid", "=", "v2_users.role_uuid")
            ->orderBy('v2_users.created_at')
            ->get();

        return $query;
    }
}
