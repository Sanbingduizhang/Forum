<?php

namespace App\Modules\Basic\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Contracts\Validation\Validator;

use App\Exceptions\ApiException;

class ApiBaseRequest extends FormRequest
{
    /**
     * @var array
     */
    protected $rules = [];

    /**
     * @var array
     */
    protected $messages = [];

    /**
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * @return array
     */
    public function messages(){
        return $this->messages;
    }

    /**
     * @return array
     */
    public function rules(){
        return $this->rules;
    }


    /**
     * @param Validator $validator
     * @throws ApiException
     */
    protected function failedValidation(Validator $validator)
    {
        if( $validator->failed() ){
//            $response_errors = json_encode($validator->getMessageBag()->all(),JSON_UNESCAPED_UNICODE);
            throw new ApiException($validator->getMessageBag()->first(),-100);
        }
    }
}
