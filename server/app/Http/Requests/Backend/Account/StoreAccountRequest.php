<?php

namespace App\Http\Requests\Backend\Account;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreAccountRequest.
 */
class StoreAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->isAdmin() OR $this->user()->isClient();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'          => ['required'],
            'street'        => ['required'],
            'city'          => ['required'],
            'state'         => ['required'],
            'zipcode'       => ['required'],
            'country'       => ['required'],
            'phone'         => ['nullable'],
            'website'       => ['nullable','url'],
            'training_date' => ['nullable','date'],
            'active'        => ['nullable','numeric'],
            'override'      => ['nullable','numeric'],
        ];
    }
}
