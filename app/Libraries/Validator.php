<?php namespace App\Libraries;

/**
 * Clase para validar campos de formulario
 * 
 * Autor: Franco Purihuaman
 * Licencia: 
 */

 use App\Libraries\InputFilter;

class Validator {

    protected $data = [];      //Array con los valores de entrada del formulario
    protected $filters = [];       //Array con las reglas para cada campo de formulario
    protected $errorMessages = [];    //Array con mensajes de descripción del error al no pasar la validación
    protected $unverifiedRules = [];  //Array con mensajes de error al encontrar reglas de validación no definidas
    protected $changeFieldName = []; //Array para cambiar los 'nombres' de los campos a una descripción


    /**
     * Validar las los campos
     * 
     * Función principal -> Este méto debe ser llamado para validar los campos
     * 
     * @param array arreglo de valores a validar
     * @return boolean 'true' si todos los campos se validaron exitosamente
     */
    public function validate($data){
        $data = $this->trimSpacesData($data);
        $data = $this->removeBadCode($data);
        $this->addData($data);
        $this->verifyRules();
        $this->changeFieldName();
        
        if(empty($this->errorMessages)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Devuelve el array de valores analizados
     * Se han eliminado los espacios del inicio y final de cada elemento
     * del array debuelto
     *
     * @return array 
     */
    public function getData(){
        return $this->data;
    }

    /**
     * Obtiene los mensajes de error de validación en un array asociativo
     * las claves del array coincide con el nombre de campo
     * 
     * @return array
     */
    public function getErrorMessages(){
        return $this->errorMessages;
    }

    /**
     * Obtiene los mensajes de error que se generaron al encontrar una regla no valida
     * las claves del array coincide con el nombre de campo
     * 
     * @return array
     */
    public function getUnverifiedRules(){
        return $this->unverifiedRules;
    }

    /**
     * Asigna los valores al array '$data' con los
     * valores a validar
     * 
     * @param array valores de entra del formulario
     * @return void 
     */
    private function addData($data){
        $this->data = $data;
    }

    
    /**
     * Verificar todas las reglas de validación asignadas
     * 
     * @return void
     */
    private function verifyRules(){
        
        foreach($this->filters as $keyRule => $rules){
            $valorEncontrado = false;
            foreach($this->data as $keyValue => $value){
                if($keyRule == $keyValue){
                    //Si el campo es diferente de vacio se verificará todas las reglas
                    //de lo contrario si esta vacio, solo se verificará si es requerido
                    if(!$this->isEmptyString($value)){
                        $array_rules = $this->stringToArrayFilters($rules);
                        if(!empty($array_rules)){
                            foreach($array_rules as $rule){
                                if(!$this->managerRules($keyValue, $value, $rule[0], $rule[1])){
                                    break;
                                }
                            }
                        }
                    }else{
                        if($this->verifyExistsRequired($rules)){
                            $this->managerRules($keyValue, '', 'required');
                        }
                    }
                    $valorEncontrado = true;
                }
            }
            //Si un campo tiene la regla 'required', el formulario esta obligado
            //a contener dicho campo
            if(!$valorEncontrado){
                if($this->verifyExistsRequired($rules)){
                    $this->managerRules($keyRule, '', 'required');
                }
            }
        }
    }


    /**
     * Convierte las reglas de formato cadena a array
     * 
     * @param string reglas
     * @return array reglas
     */
    private function stringToArrayFilters($string_rules){
        $output_rules = [];
        $string_rules = !empty($string_rules) ? str_replace(' ','',$string_rules) : '';
        if($string_rules != ''){
            $array_rules = explode('|', $string_rules);
            foreach($array_rules as $key => $rule){
                if(strpos($rule, ':')){
                    $array = explode(':', $rule);
                    $rule = $array[0];
                    unset($array[0]);
                    $paramRules = array_values($array);
                    $output_rules[] = [$rule, $paramRules];
                }else{
                    $output_rules[] = [$rule, ''];
                }
            }
        }
        return $output_rules;
    }

    /**
     * Ejecuta la validación según la regla dada
     * Si no encuentra la regla, agrega un mensaje de error en el array '$unverifiedRules'
     * 
     * @param string nombre del campo
     * @param string valor del campo de formulario
     * @param string regla a aplicar
     * @param array parametros de regla
     * @return boolean verifica si se valido con exito
     */
    private function managerRules($input, $valor, $rule ,$array_paramRule = []){
        switch($rule){
            case 'required':
                if($this->isEmptyString($valor)){
                    $this->errorMessages[$input] = "El campo {$input} es requerido";
                    return false;
                }else{
                    return true;
                }break;
            case 'integer':
                if(!$this->verifyType($valor, $rule)){
                    $this->errorMessages[$input] = "El campo {$input} debe ser un número entero";
                    return false;
                }else{
                    return true;
                }break;
            case 'double':
                if(!$this->verifyType($valor, $rule)){
                    $this->errorMessages[$input] = "El campo {$input} debe ser un número decimal";
                    return false;
                }else{
                    return true;
                }break;
            case 'numeric':
                if(!$this->verifyType($valor, 'integer')){
                    if(!$this->verifyType($valor, 'double')){
                        $this->errorMessages[$input] = "El campo {$input} debe ser un número";
                        return false;
                    }else{
                        return true;
                    }
                }else{
                    return true;
                }break;
            case 'date':
                if(!$this->verifyType($valor, $rule)){
                    $this->errorMessages[$input] = "El campo {$input} debe ser una fecha";
                    return false;
                }else{
                    return true;
                }break;
            case 'length':
                if(!$this->verifyLengthStirng($valor, $array_paramRule[0])){
                    $this->errorMessages[$input] = "El campo {$input} debe tener {$array_paramRule[0]} digitos";
                    return false;
                }else{
                    return true;
                }break;
            case 'min':
                if(!$this->verifyMinLengthStirng($valor, $array_paramRule[0])){
                    $this->errorMessages[$input] = "El campo {$input} debe tener {$array_paramRule[0]} digitos como minimo";
                    return false;
                }else{
                    return true;
                }break;
            case 'max':
                if(!$this->verifyMaxLengthStirng($valor, $array_paramRule[0])){
                    $this->errorMessages[$input] = "El campo {$input} debe tener {$array_paramRule[0]} digitos como maximo";
                    return false;
                }else{
                    return true;
                }break;
            case 'email':
                if(!$this->isValidEmail($valor)){
                    $this->errorMessages[$input] = "El campo {$input} debe contener e-mail valido";
                    return false;
                }else{
                    return true;
                }break;
            case 'values':
                if(!$this->isValidValue($valor, $array_paramRule)){
                    $this->errorMessages[$input] = "El campo {$input} debe cotener una opción válida";
                    return false;
                }else{
                    return true;
                }break;
            default:
                $this->unverifiedRules[$input] = "No existe la regla {$rule}";
        }
    }

    /**
     * Verifica si un valor es de un tipo dado
     * Los valores que verifica son: integer, double, date, string
     * 
     * @param  mixed valor a verificar'
     * @param string tipo de dato a verificar
     * @return boolean 
     */
    private function verifyType($valor, $tipo){
        if($tipo == $this->getType($valor)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Verifica si la longitud de una cadena es igual a la dada
     * 
     * @param string cadena a verificar
     * @param string longitud a verificar
     * @return boolean
    */
    private function verifyLengthStirng($string, $length){
        if(strlen($string) == $length){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Verifica si la longitud de una cadena es como minimo
     * la longitud dada
     * 
     * @param string cadena a verificar
     * @param string longitud a verificar
     * @return boolean
    */
    private function verifyMinLengthStirng($string, $length){
        if(strlen($string) >= $length){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Verifica si la longitud de una cadena es como maximo
     * la longitud dada
     * 
     * @param string cadena a verificar
     * @param string longitud a verificar
     * @return boolean
    */
    private function verifyMaxLengthStirng($string, $length){
        if(strlen($string) <= $length){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Verifica si la cadena es un email valido
     * 
     * @param string cadena a verificar
     * @return boolean
    */
    private function isValidEmail($email){
        if(filter_var($email, FILTER_VALIDATE_EMAIL)){
            return true;
        }else{
            return false;
        }
    }


    /**
     * Verifica si el valor de una variable pertenece
     * a la lista de valores dada
     * 
     * @param string valor a buscar
     * @param array valores al que debe pertenecer la cadena a buscar
     * @return boolean
    */
    private function isValidValue($variable, $values){
        $isValid = false;

        if(is_array($values)){
            foreach($values as $valor){
                if($variable == $valor){
                    return true;
                }
            }
        }
        return $isValid;
    }

    /**
     * Verifica si una cadena esta vacia
     * 
     * @param string cadena a verificar
     * @return boolean
    */
    private function isEmptyString($value){
        $value = trim($value);
        if(strlen($value) > 0){
            return false;
        }else{
            return true;
        }
    }

    /**
     * Conseguir el tipo de dato de un valor
     * los tipos obtenidos son: integer, double, date, string
     * 
     * @param string valor a verificar
     * @return string tipo de dato
     */
    private function getType($valor){
        $valor = trim($valor);
        
        if(is_numeric($valor)){
            if(ctype_digit($valor)){
                return 'integer';
            }else{
                return 'double';
            }
        }else if($this->isDate($valor)){
            return 'date';
        }else{
            return 'string';
        }
    }

    /**
     * Verifica que una cadena sea una fecha valida
     * 
     * @param string fecha a evaluar en formato 'year-month-day'
     * @return boolean
     */
    private function isDate($valor){
        $valor = str_replace(' ', '', $valor);
        $valor = explode('-', $valor);
        if(count($valor) == 3){
            //checkdate() verifica si una fecha conformada por los datos
            //entregados es valida => En formato gregoriano mes/dia/año
            if(checkdate($valor[1], $valor[2], $valor[0])){
                return true;
            }
        }
        return false;
    }
    
    /**
     * Verifica si una cadena tiene la palabra 'required'
     * 
     * @param string cadena a evaluar
     * @return boolean
     */
    private function verifyExistsRequired($string){
        
        $string = str_replace(' ', '', $string);

        if(empty($string)){
            return false;
        }else if(strpos($string, 'required') !== false){
            return true;
        }else{
            return false;
        }
    }


    /**
     * Remueve espacios en blanco del inicio y final de cada
     * elemento de un array
     * 
     * @return void
     */
    private function trimSpacesData($data){
        foreach($data as $key => $value){
            $data[$key] = $this->trim($value);
        }
        return $data;
    }


    /**
     * Convierte un valor a estring y quita los espacios
     * del inicio y final.
     * 
     * @param mixed valor a evaluar
     * @return string 
     */
    private function trim($value){
        $value = (string) $value;
        $value = trim($value);
        return $value;
    }


    /**
     * Remueve codigo dañino por ataque xss
     * 
     * @param array array de datos a filtrar
     * @return array array de datos filtrados
     */
    private function removeBadCode($data){
        $oInputFilter = new InputFilter();
        $data = $oInputFilter->process($data);
        return $data;
    }


    /**
     * Cambia los nombres de los campos de entrada por una descripción
     * 
     * @return void
     */
    private function changeFieldName(){
        if(!empty($this->changeFieldName)){
            if(!empty($this->errorMessages)){
                foreach($this->changeFieldName as $key_change => $value_change){
                    foreach($this->errorMessages as $key_error => $value_error){
                        if($key_change == $key_error){
                            $this->errorMessages[$key_error] = str_replace($key_change, $value_change, $value_error);
                            break;
                        }
                    }
                }
            }
        }
    }

}