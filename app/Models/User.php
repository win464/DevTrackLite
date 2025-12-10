<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Enums\UserRole;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Projects where this user is a team member
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_user')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * Milestones assigned to this user
     */
    public function milestones()
    {
        return $this->belongsToMany(Milestone::class, 'milestone_user')
                    ->withTimestamps();
    }

    /**
     * Check if the user has one of the given roles.
     */
    public function hasRole(string|array $roles): bool
    {
        $current = $this->role ? UserRole::from($this->role) : UserRole::VIEWER;

        if (is_string($roles)) {
            $roles = preg_split('/[|,]/', $roles, flags: PREG_SPLIT_NO_EMPTY);
        }

        $allowed = array_map(fn($r) => is_string($r) ? UserRole::from($r) : $r, (array) $roles);

        return in_array($current, $allowed, true);
    }
}
