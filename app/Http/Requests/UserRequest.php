<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $userId = $this->route('user') ? $this->route('user')->id : null;

        return [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $userId,
            'username' => 'nullable|string|unique:users,username,' . $userId,
            'phone'    => 'nullable|string|unique:users,phone,' . $userId,
            'role'     => 'nullable|array',
            'role.*'   => 'exists:roles,name', 
            'avatar'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status'   => 'required|string|in:active,inactive,suspended',
            'password' => $userId ? 'nullable|min:8' : ['required', Password::defaults()],
            'settings' => 'nullable|array',
        ];
    }
}