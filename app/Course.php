<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    public $timestamps = false;

    public function students()
    {
        return $this->belongsToMany('App\Student', 'student_course');
    }
}
