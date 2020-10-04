<?php namespace App\Rules;

use App\Libraries\Validator;

class UserRules extends Validator{

    protected $filters = [
        'USU_NOMBRE' => 'required',
        'USU_USER' => 'required',
        'USU_PASS' => 'required|min:6',
        'USU_ESTADO' => 'required',
        'USU_ACTIVO' => 'required',
        'UG_ID' => 'required'
    ];

    protected $changeFieldName = [
        'USU_NOMBRE' => 'nombre',
        'USU_USER' => 'usuario',
        'USU_PASS' => 'contraseña',
        'USU_ESTADO' => 'estado',
        'USU_ACTIVO' => 'activo',
        'UG_ID' => 'grupo'

    ];

}