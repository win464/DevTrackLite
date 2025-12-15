<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        // All authenticated users can view projects (filtered by role in controller)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project)
    {
        // Admin can view all
        if ($user->role === 'admin') {
            return true;
        }
        
        // Owner can view their own projects
        if ($project->owner_id === $user->id) {
            return true;
        }
        
        // Can view if assigned as team member
        if ($project->teamMembers()->where('user_id', $user->id)->exists()) {
            return true;
        }
        
        // Can view if assigned to any milestone in the project
        return $project->milestones()->whereHas('assignedUsers', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        // Only managers and admins can create projects
        return in_array($user->role, ['admin', 'manager']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project)
    {
        // Admin can update any project; managers can update owned projects
        return $user->role === 'admin' || 
               ($user->role === 'manager' && $project->owner_id === $user->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project)
    {
        // Admin can delete any project; managers can delete owned projects
        return $user->role === 'admin' || 
               ($user->role === 'manager' && $project->owner_id === $user->id);
    }
}
