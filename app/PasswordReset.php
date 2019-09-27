<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $table = 'password_resets';

    public $timestamps = false;

    protected $dates =[
        'created_at'
    ];

    public $fillable = ['email', 'token'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function($model){
            $model->setCreatedAt($model->freshTimeStamp());
        });
    }

    public function getExpiredAttribute()
    {
        $tokenCreatedTime = $this->created_at;

        $tokenExpiryTime = Carbon::createFromTimestamp(time())->subHours(2);

        if($tokenCreatedTime->gt($tokenExpiryTime))
        {
            return true;
        }

        return false;
    }
}