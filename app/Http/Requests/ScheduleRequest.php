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
            'type'           => 'required|string|in:meeting,appointment,task',
            'title'          => 'required|string|max:255',
            'link'           => 'nullable|url',
            'date'           => 'required|date',
            'start_time'     => 'required',
            'end_time'       => 'required|after:start_time',
            'participants'   => 'nullable|array',
            'participants.*' => 'nullable|email',
            'location'       => 'nullable|string',
            'room'           => 'nullable|string',
            'description'    => 'nullable|string',
            'attachment'     => 'nullable|file|mimes:pdf|max:5120',
            'color_id'       => 'nullable|string',
        ];
    }
}
