<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request; 

class ScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'type'         => $this->type,
            'link'         => $this->link,
            'title'        => $this->title,
            'date'         => $this->date,
            'start_time'   => $this->start_time,
            'end_time'     => $this->end_time,
            'participants' => $this->participants,
            'location'     => $this->location,
            'room'         => $this->room,
            'description'  => $this->description,
            'color_id'     => $this->color_id,
            'created_at'   => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
