<?php namespace App\Controllers;

use App\Libraries\Session;
use App\Models\User;
use App\Libraries\View;

class AccessController {

    public function login(){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $user = $_POST['user'];
            $password = $_POST['pass'];

            $user = trim($user);
            $password = trim($password);
            
            $oUser = new User;
            $resultSet = $oUser->join('USUARIO_GRUPO UG', 'USU.UG_ID', '=', 'UG.UG_ID')
                                ->select(['USU.USU_ID', 'USU.USU_NOMBRE', 'USU_PASS', 'UG.UG_ID', 'UG.UG_NOMBRE'])
                                ->where('USU_USER', '=', $user)
                                ->first();
            
            if(!empty($resultSet)){

                if($password == $resultSet->USU_PASS){

                    $usuario = [];
                    $usuario['id'] = $resultSet->USU_ID;
                    $usuario['nombre'] = $resultSet->USU_NOMBRE;
                    $usuario['rol_id'] = $resultSet->UG_ID;
                    $usuario['rol_nombre'] = $resultSet->UG_NOMBRE;

                    Session::start();
                    Session::add('user', $usuario);
                    View::redirection('panel');
                }else{
                    $errors[] = 'Usuario o contraseña invalidos';
                    View::render('pages/userLogin', compact('errors'));
                }
                
            }else{
                $errors[] = 'Usuario o contraseña invalidos';
                View::render('pages/userLogin', compact('errors'));
            }
        }else{
            View::render('pages/userLogin');
        }
    }
}