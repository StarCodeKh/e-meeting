<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type', 'title', 'date', 'start_time', 'end_time', 
        'participants', 'location', 'room', 'description', 'link', 'attachment', 'color_id', 'user_id'
    ];

    protected $casts = [
        'participants' => 'array',
        'date' => 'date:Y-m-d',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    /**
     * Relationship ទៅកាន់ User ដែលជាអ្នកបង្កើត
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship សម្រាប់ទាញព័ត៌មានពណ៌ (Priority)
     * បងត្រូវប្រាកដថាមាន Model ឈ្មោះ Priority ឬប្តូរវាទៅតាម Model ពិតប្រាកដរបស់បង
     */
    public function color(): BelongsTo
    {
        return $this->belongsTo(Priority::class, 'color_id', 'slug');
    }
}