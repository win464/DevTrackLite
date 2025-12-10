<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'title', 'description', 'deadline', 'status', 'budget', 'spent'
    ];

    protected $casts = [
        'deadline' => 'date',
        'budget' => 'decimal:2',
        'spent' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'milestone_user')
                    ->withTimestamps();
    }

    public function isOverdue(): bool
    {
        return $this->deadline && $this->status !== 'completed' && now()->greaterThan($this->deadline);
    }
}
