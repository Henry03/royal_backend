<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = "admin";

    protected $fillable = [
        'id',
        'username',
        'password',
        'role'
    ];

    protected $hidden = [
        'password',
    ];
}
