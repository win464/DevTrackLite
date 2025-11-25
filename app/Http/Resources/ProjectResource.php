<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'owner_id' => $this->owner_id,
            'budget' => $this->budget !== null ? (float) $this->budget : null,
            'spent' => $this->spent !== null ? (float) $this->spent : null,
            'progress' => $this->progress ?? 0,
            'overdue' => (bool) ($this->overdue ?? false),
            'over_budget' => (bool) ($this->over_budget ?? false),
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
