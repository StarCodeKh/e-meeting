<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'user_id'       => $this->user_id,
            'name'          => $this->name,
            'email'         => $this->email,
            'phone'         => $this->phone ?? 'N/A',
            'role'          => strtoupper($this->role),
            'status'        => $this->status,
            'avatar_url'    => $this->avatar ? asset('storage/' . $this->avatar) : null,
            'last_login'    => $this->last_login_at?->diffForHumans() ?? 'Never',
            'settings'      => $this->settings ?? (object)[],
            'created_at'    => $this->created_at->format('Y-m-d H:i'),
        ];
    }
}
