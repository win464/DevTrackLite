<?php

namespace App\Policies;

use App\Models\Milestone;
use App\Models\User;

class MilestonePolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Milestone $milestone)
    {
        // Can view if can view the parent project
        $project = $milestone->project;
        
        if ($user->role === 'admin') {
            return true;
        }
        
        if ($user->role === 'manager') {
            return $project->owner_id === $user->id;
        }
        
        // Viewer: can view if assigned to project
        return $project->teamMembers()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create milestones.
     */
    public function create(User $user, $project)
    {
        // Admin or project owner can create milestones
        return $user->role === 'admin' || $project->owner_id === $user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Milestone $milestone)
    {
        // Admin or project owner can fully update
        return $user->role === 'admin' || $milestone->project->owner_id === $user->id;
    }

    /**
     * Determine whether the user can update only the status.
     */
    public function updateStatus(User $user, Milestone $milestone)
    {
        // Admin, project owner, or assigned user can update status
        $isOwner = $milestone->project->owner_id === $user->id;
        $isAdmin = $user->role === 'admin';
        $isAssigned = $milestone->assignedUsers()->where('user_id', $user->id)->exists();
        
        return $isAdmin || $isOwner || $isAssigned;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Milestone $milestone)
    {
        // Admin or project owner can delete
        return $user->role === 'admin' || $milestone->project->owner_id === $user->id;
    }
}
