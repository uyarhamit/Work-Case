<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreEventsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $limit = Request::input('attendance_limit');
        return [
            'title' => 'required|string|max:255',
            'start_date' => 'required|date_format:Y-m-d H:i|after_or_equal:today',
            'duration' => 'required|numeric',
            'attendance_limit' => 'required|numeric',
            'speakers' => 'required|array|min:1',
            'attendees' => 'required|array|min:1|max:' . $limit
        ];
    }

    public function messages()
    {
        return [
            'speakers' => 'Event has to be at least 1 speaker!',
            'attendees' => 'Max attendees must not be greater than attendance limit!',
        ];
    }
}
