<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleType extends Model
{
    protected $fillable = [
        'name', 'slug', 'color_hex', 'color_gradient', 
        'icon', 'is_active', 'sort_order'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Scope សម្រាប់ទាញយកតែប្រភេទណាដែលប្រើប្រាស់បាន
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}