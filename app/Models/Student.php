<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Inertia\Inertia;

class Student extends Model
{
    protected $table = 'students';

    protected $fillable = [
        'student_id',
        'name',
        'course',
        'year',
        'adviser',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }


}
