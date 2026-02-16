<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = ['key_name', 'label', 'sort_order', 'is_active', 'icon', 'description'];
    public function permissions()
    {
        return $this->hasMany(Permission::class, 'module_key', 'key_name');
    }
}