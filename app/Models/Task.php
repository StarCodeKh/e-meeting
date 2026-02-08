<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model {
    protected $fillable = [
        'schedule_id',
        'title',
        'description',
        'status',
        'priority',
        'assigned_to'
    ];

    public function schedule() { 
        return $this->belongsTo(Schedule::class); 
    }
    public function assignee() { 
        return $this->belongsTo(User::class, 'assigned_to'); 
    }
}