<?php

namespace App\Http\Requests\Backend\Product;

use Illuminate\Foundation\Http\FormRequest;

class ExportProductRequest extends FormRequest
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
            'date-picker-start-date'    => ['before_or_equal:date-picker-end-date', 'required'],
            'date-picker-end-date'      => ['after_or_equal:date-picker-start-date', 'required'],
        ];
    }

    public function attributes()
    {
        return [
            'date-picker-start-date'    => 'start date',
            'date-picker-end-date'      => 'end date',
        ];
    }
}
