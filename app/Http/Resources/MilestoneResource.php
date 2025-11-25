<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MilestoneResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'title' => $this->title,
            'description' => $this->description,
            'deadline' => optional($this->deadline)?->toDateString(),
            'status' => $this->status,
            'budget' => $this->budget !== null ? (float) $this->budget : null,
            'spent' => $this->spent !== null ? (float) $this->spent : null,
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
        ];
    }
}
