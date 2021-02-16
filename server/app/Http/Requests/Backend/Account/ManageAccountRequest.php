<?php

namespace App\Http\Requests\Backend\Account;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class ManageAccountRequest.
 */
class ManageAccountRequest extends FormRequest
{
    /**
     * Determine if the Account is authorized to make this request.
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
            /*
            'name'   => ['required'],
            'street' => ['required'],
            'city'   => ['required'],
            'state'   => ['required'],
            'zipcode' => ['required'],
            'country' => ['required'],
            'website'   => ['required'],
            'training_date' => ['required'],
            'active' => ['required']
            */
        ];
    }
}
