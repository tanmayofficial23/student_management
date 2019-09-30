<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name', 'email_id', 'phone_no',
    ];

    public function courses()
    {
        return $this->belongsToMany('App\Course', 'student_course');
    }
}
