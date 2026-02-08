<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SchedulePolicy
{
    public function update(User $user, Schedule $schedule) {
        return $user->id === $schedule->created_by || $user->hasRole('admin');
    }
}
