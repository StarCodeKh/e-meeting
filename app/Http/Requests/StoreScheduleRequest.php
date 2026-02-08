<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules()
    {
        return [
            'title'       =>'required|string|max:255',
            'description' =>'nullable|string',
            'start_at'    =>'required|date',
            'end_at'      =>'nullable|date|after_or_equal:start_at',
            'repeat_rule' =>'nullable|string',
        ];
    }
}
