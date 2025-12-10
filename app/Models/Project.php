<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'owner_id',
        'status',
        'start_date',
        'end_date',
        'budget',
        'spent',
    ];

    protected $appends = ['progress', 'overdue', 'over_budget'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function milestones()
    {
        return $this->hasMany(Milestone::class);
    }

    public function teamMembers()
    {
        return $this->belongsToMany(User::class, 'project_user')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    public function getProgressAttribute(): int
    {
        $total = $this->milestones()->count();
        if ($total === 0) return 0;
        $done = $this->milestones()->where('status', 'completed')->count();
        return (int) floor(($done / $total) * 100);
    }

    public function getOverdueAttribute(): bool
    {
        return $this->milestones()->where('status', '!=', 'completed')
            ->whereNotNull('deadline')
            ->where('deadline', '<', now()->toDateString())
            ->exists();
    }

    public function getOverBudgetAttribute(): bool
    {
        if (! $this->budget) return false;
        $spent = $this->milestones()->sum('spent');
        return $spent > $this->budget;
    }
}
