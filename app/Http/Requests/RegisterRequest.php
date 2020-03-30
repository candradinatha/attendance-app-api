<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $required = $this->isMethod('post') ? 'required' : 'nullable';
        $id = optional($this->user())->id;
        return [
            'name' => 'required|min:2|max:50',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'required|numeric|digits_between:8,20|unique:users,phone,' . $id,
            'employee_id' => 'required|numeric|digits_between:3,5|unique:users,employee_id,' . $id,
            'password'   => $required . '|min:6|max:50'
        ];
    }
}
