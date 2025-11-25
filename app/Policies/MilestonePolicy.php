<?php

namespace App\Policies;

use App\Models\Milestone;
use App\Models\User;

class MilestonePolicy
{
    public function update(User $user, Milestone $milestone)
    {
        // Admin or project owner can update
        return $user->role === 'admin' || $milestone->project->owner_id === $user->id;
    }

    public function delete(User $user, Milestone $milestone)
    {
        // Admin or project owner can delete
        return $user->role === 'admin' || $milestone->project->owner_id === $user->id;
    }
}
