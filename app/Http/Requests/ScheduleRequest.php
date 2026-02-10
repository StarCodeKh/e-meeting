<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'type'         => 'required|string|in:meeting,personal,task',
            'title'        => 'required|string|max:255',
            'link'         => 'nullable|url',
            'date'         => 'required|date',
            'start_time'   => 'required',
            'end_time'     => 'required|after:start_time',
            'participants' => 'nullable|array',
            'location'     => 'nullable|string',
            'room'         => 'nullable|string',
            'color_id'     => 'nullable|string',
        ];
    }
}
