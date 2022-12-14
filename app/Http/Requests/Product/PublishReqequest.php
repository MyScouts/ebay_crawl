<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class PublishReqequest extends FormRequest
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
        return [
            'description' => ['required', 'regex:/^[0-9\.\-\/ ]+$/']
        ];
    }

    public function messages()
    {
        return [
            'description.regex'   => "Description only allow number, please select correct input!"
        ];
    }
}
