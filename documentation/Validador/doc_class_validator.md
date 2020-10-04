
* Documentación de la clase 'Validator'. Esta clase sirve para validar campos de formulario
* Autor: Franco Purihuaman
* Licencia: 

# Esta clase verifica si el valor de un campo es:
1) requerido : regla => 'required'
2) entero : regla => 'integer'
3) decimal : regla => 'double'
4) Numeric : regla => 'numeric'
5) fecha : regla => 'date'
6) de una longitud dada : regla => 'length:3'
7) de una longitud minima dada : regla => 'min:4'
8) de una longitud maxima dada : regla => 'max:10'
9) un email valido : regla => 'email'
10) pertenece a una lista de valores : regla => 'values:masculino:femenino'

Nota: Si un campo tiene mas de una regla se separa con un |
 
# Forma de uso

1)  La clase que estienda a esta clase "Validator" debe definir una propiedad protected con el nombre "filters". Este debe ser un array con las reglas de validación.
    Se debe tener en cuenta:

Ejemplo: 

    protected $filters = [
        'USU_NOMBRE' => 'required',
        'USU_USER' => 'required',
        'USU_PASS' => 'required|min:6',
        'USU_ESTADO' => 'required',
        'USU_ACTIVO' => 'required',
        'UG_ID' => 'required'
    ];

2)  Opcionalmente se puede redefinir el array 'nameFieldToDescription'. Esto con el fin de que si hay errores de validación, se cree un mensaje de validación personalizado para cada campo

Ejemplo:

    protected $nameFieldToDescription = [
        'USU_NOMBRE' => 'nombre',
        'USU_USER' => 'usuario',
        'USU_PASS' => 'contraseña',
        'USU_ESTADO' => 'estado',
        'USU_ACTIVO' => 'activo',
        'UG_ID' => 'grupo'
    ];

>En el supuesto que el campo USU_PASS no cumple la regla de tener como minimo 6 digitos, se lanzara el siguiente mensaje de validación.
"El campo contraseña debe tener como minimo 6 digitos" 
    

3)  Para ejecutar el validador:

  - Se debe ejecutar el método 'validate()' y se le debe pasar como parametro un array asociativo de valores a validar (NOTA: La clve del valor a validar debe coincidir con la clave del filtro a aplicar). Este debuelve 'true' si se valido con éxito, si hubo errores de validación debuelve 'false'

Ejemplo:

        // Array de reglas de validaciòn
        protected $filters = [
            'USU_CODIGO' => 'required|integer|length:5'
        ];

        // Array de valores a validar
        data['USU_CODIGO' => '98765']

        // validación
        succes = $this->validate($arrayValues);

- Si se valido con éxito, se debe ejecutar el método 'getData()' para obtener los valores validados (Estos valores fuerón pasados por filtros para eliminar ataques xss)

- Si hubo errores de validación se debe ejecutar el método 'getMsgErrorsValidation()' para obtener los mensajes de los errores

 Ejemplo:

        if($this->validate($data))
        {
            $data = $this->getData();   
        }else{
            $errors = $this->getErrorMessages();
        }
    

4)  NOTA: Podemos llamar al método 'getUnverifiedRules', este método almacena todas las reglas que el validador no pudo verificar ya sea porque la escribimos mal o porque la regla no es soportado por el verificador

Ejemplo: 

	$reglasNoVerificadas = $validador->getUnverifiedRules();

---