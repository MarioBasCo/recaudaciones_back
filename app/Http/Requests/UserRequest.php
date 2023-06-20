<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'nullable',
            'persona.identificacion' => 'required',
            'persona.nombres' => 'required',
            'persona.apellidos' => 'required',
            'persona.direccion' => 'required',
            'persona.celular' => 'required',
            'role' => 'required'
        ];
    }

    public function messages() {

        return [
            'name.required'         => 'El usuario es obligatorio.',
            'email.required'    => 'El correo es obligatorio.',
            'email.email'         => 'El correo debe tener un formato valido.',
            'role.required'    => 'El rol es obligatorio.',
            'persona.nombres.required'  => 'El campo nombres es obligatorio.',
            'persona.apellidos.required'       => 'El campo apellidos es obligatorio.',
            'persona.direccion.required'           => 'El campo dirección es obligatorio.',
            'persona.celular.required'        => 'El campo teléfono es obligatorio.',
        ];
    }
}
