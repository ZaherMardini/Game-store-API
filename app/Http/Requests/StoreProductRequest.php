<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
      return Gate::allows('Super user'); // test this
      // return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
      return [
        'name' => ['required', 'string', 'min:5'],
        'description' => ['required', 'string', 'min:5'],
        'price' => ['required','numeric','min:20'],
        'image' => ['required'],
        'categories' => ['sometimes','array'],
        'categories.*' => ['integer', 'exists:categories,id']
      ];
    }
}


