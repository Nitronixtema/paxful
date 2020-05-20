<?php

namespace App\Http\Requests;

use App\Http\Requests\Base as FormRequest;

class CreateTransaction extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'amount' => 'required|numeric|min:0.001',
            'from' => 'required|uuid',
            'to' => 'required|uuid|different:from'
        ];
    }
}
