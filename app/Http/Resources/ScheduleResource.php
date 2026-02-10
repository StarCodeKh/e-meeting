<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request; 
use Carbon\Carbon;

class ScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'type'         => $this->type,
            'title'        => $this->title,
            'start_time'   => $this->start_time,
            'end_time'     => $this->end_time,
            'participants' => $this->participants ?? [],
            'location'     => $this->location,
            'description'  => $this->description ?? 'មិនមានការពិពណ៌នា',
            'link'         => $this->link,
            'date'         => $this->date,
            'room'         => $this->room,
            'color_id'     => $this->color_id,
            'created_at'   => $this->created_at->format('Y-m-d H:i:s'),
        ];

        
    }
    
}
