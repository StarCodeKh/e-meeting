<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($setting) {
            Cache::forget("setting_{$setting->key}");
            Cache::forget("settings_group_{$setting->group}");
        });
    }

    /**
     * Dynamic Get: ទាញយកតាម Key នីមួយៗ
     */
    public static function get($key, $default = null)
    {
        $key = Str::lower($key);
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Dynamic Set: រក្សាទុកដោយកំណត់ Group (Standard: general)
     */
    public static function set($key, $value, $group = 'general')
    {
        $key = Str::lower($key);
        return self::updateOrCreate(
            ['key' => $key], 
            [
                'value' => is_array($value) ? json_encode($value) : (string) $value,
                'group' => Str::lower($group)
            ]
        );
    }

    /**
     * Standard Group Fetch: ទាញយក Setting ទាំងអស់ក្នុង Group តែមួយ (មានប្រសិទ្ធភាពខ្ពស់)
     */
    public static function getByGroup($group)
    {
        $group = Str::lower($group);
        return Cache::remember("settings_group_{$group}", 3600, function () use ($group) {
            return self::where('group', $group)->pluck('value', 'key')->toArray();
        });
    }
}