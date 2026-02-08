<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'schedule_id' => $this->schedule_id,
            'title'       => $this->title,
            'description' => $this->description,
            'status'      => $this->status,
            'priority'    => $this->priority,
            'assigned_to' => $this->assigned_to,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            'schedule'    => $this->whenLoaded('schedule'),
        ];
    }
}
