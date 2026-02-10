<?php

// app/Models/Schedule.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type', 'title', 'date', 'start_time', 'end_time', 
        'participants', 'location', 'room', 'color_id', 'user_id'
    ];

    protected $casts = [
        'participants' => 'array',
        'date' => 'date:Y-m-d',
    ];
}