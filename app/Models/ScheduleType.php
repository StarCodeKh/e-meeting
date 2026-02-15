<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleType extends Model
{
    protected $fillable = ['name', 'slug', 'color_hex', 'color_gradient', 'icon', 'is_active', 'sort_order'];
}
