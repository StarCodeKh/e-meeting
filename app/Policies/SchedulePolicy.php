<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;

class SchedulePolicy
{
    /**
     * Determine if the user can update the schedule.
     */
    public function update(User $user, Schedule $schedule): bool
    {
        // Dynamic Check: 
        // 1. Is the user an Admin? OR
        // 2. Is the user the person who created this record?
        return $user->role === 'admin' || $user->id === $schedule->user_id;
    }

    public function delete(User $user, Schedule $schedule): bool
    {
        return $this->update($user, $schedule);
    }
}