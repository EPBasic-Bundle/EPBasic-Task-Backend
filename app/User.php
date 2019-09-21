<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'surname', 'email', 'password', 'updated_at'
    ];

    protected $hidden = [
        'role', 'password', 'remember_token', 'created_at', 'updated_at'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
