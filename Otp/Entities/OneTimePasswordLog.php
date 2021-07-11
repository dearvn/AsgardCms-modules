<?php

namespace Modules\Otp\Entities;

use Illuminate\Database\Eloquent\Model;

class OneTimePasswordLog extends Model
{
    protected $table = 'otp_password__logs';
    
    protected $fillable = ["user_id", "otp_code", "status", "refer_number", "otp_max_failed"];
}
