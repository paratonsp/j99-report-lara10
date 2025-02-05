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
        $query = DB::table("users")
            ->select('users.uuid','users.name','users.email','users.role_uuid','role.title as rolename')
            ->join("v2_role AS role", "role.uuid", "=", "users.role_uuid")
            ->orderBy('users.created_at')
            ->get();

        return $query;
    }
}
