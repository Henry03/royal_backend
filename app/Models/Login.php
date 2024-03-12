<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Login extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'login_access';
    protected $fillable = [
        'id_staff', 
        'otp',
        'exp_date',
        'token'
    ];

    // const UPDATED_AT = false;


}
