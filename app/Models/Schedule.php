<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model {
    protected $fillable = [
        'created_by',
        'title',
        'description',
        'start_at',
        'end_at',
        'priority'
    ];

    public function creator() { 
        return $this->belongsTo(User::class, 'created_by'); 
    }

    public function tasks() { 
        return $this->hasMany(Task::class); 
    }
}
