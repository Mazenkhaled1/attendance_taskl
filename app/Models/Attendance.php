<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
    
    protected $fillable = ['student_id', 'session_id'];
    
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
    
    public function session()
    {
        return $this->belongsTo(Session::class);
    }
}


