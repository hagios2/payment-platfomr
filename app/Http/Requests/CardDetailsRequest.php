<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CardDetailsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'number' => 'bail|numeric|required',
            'exp_month' => 'bail|integer|required',
            'exp_year' => 'bail|required|integer',
            'cvc' => 'bail|required|string',
        ];
    }
}
