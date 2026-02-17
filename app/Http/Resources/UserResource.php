<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

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
            'role'          => $this->roles->pluck('name')->first() ?: 'user',
            'permissions'   => $this->getAllPermissions()->pluck('name')->values(),
            'status'        => $this->status,
            'avatar_url'    => $this->avatar ? asset('storage/' . $this->avatar) : null,
            'last_login'    => $this->last_login_at?->diffForHumans() ?? 'Never',
            'settings'      => $this->settings ?? (object)[],
            'created_at'    => $this->created_at->format('Y-m-d H:i'),
        ];
    }

}
