<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;

class ScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $emails = is_array($this->participants) ? $this->participants : json_decode($this->participants, true) ?? [];
        $participantNames = User::whereIn('email', $emails)->pluck('name')->toArray();

        return [
            'id'           => $this->id,
            'type'         => $this->type,
            'title'        => $this->title,
            'start_time'   => $this->start_time ? Carbon::parse($this->start_time)->format('H:i') : null,
            'end_time'     => $this->end_time ? Carbon::parse($this->end_time)->format('H:i') : null,
            'participants' => $participantNames, 
            'participant_emails' => $emails,
            'location'     => $this->location,
            'description'  => $this->description ?? 'មិនមានការពិពណ៌នា',
            'link'         => $this->link,
            'date'         => $this->date ? Carbon::parse($this->date)->format('Y-m-d') : null,
            'room'         => $this->room,
            'color_id'     => $this->color_id,
            'attachment'   => $this->attachment ? asset('storage/' . $this->attachment) : null,
            'created_at'   => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}