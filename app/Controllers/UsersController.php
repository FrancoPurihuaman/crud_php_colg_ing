<?php namespace App\Controllers;

use App\Models\User;
use App\Models\UserGroup;
use App\Rules\UserRules;
use App\Pdf\UserPdf;
use App\Excel\UserExcel;
use App\Libraries\Session;
use App\Libraries\View;

class UsersController extends UserRules{

    public function show(){
        $oUser = new User;
        $users = $oUser->join('USUARIO_GRUPO UG', 'USU.UG_ID', '=', 'UG.UG_ID')
                        ->select(['USU.USU_ID', 'USU.USU_NOMBRE', 'USU.USU_USER', 'UG.UG_NOMBRE'])
                        ->orderBy('USU.USU_ID', 'ASC')
                        ->get();
        View::render('pages/userList', compact('users'));
    }
    
    public function create(){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $data['USU_NOMBRE'] = $_POST['nombre']??'';
            $data['USU_USER'] = $_POST['user']??'';
            $data['USU_PASS'] = $_POST['pass']??'';
            $data['USU_ESTADO'] = $_POST['estado']??'';
            $data['USU_ACTIVO'] = $_POST['activo']??'';
            $data['UG_ID'] = $_POST['grupo']??'';

            if($this->validate($data)){

                $data = $this->getData();
                
                $oUser = new User;
                $exito = $oUser->insert($data);
                
                $msj = "";
                if($exito){
                    $msj = 'El usuario se guardó exitosamente';
                }else{
                    $msj = 'Algo salio mal. No se guardó el usuario';
                }

                $oUG = new UserGroup;
                $grupos = $oUG->select()->get();
                View::render('pages/userCreate', compact('exito','msj', 'grupos'));
            }else{
                $errors = $this->getErrorMessages();

                $oUG = new UserGroup;
                $grupos = $oUG->select()->get();
                View::render('pages/userCreate', compact('errors','grupos'));
            }

        }else{
            $oUG = new UserGroup;
            $grupos = $oUG->select()->get();
            View::render('pages/userCreate', compact('grupos'));
        }
    }

    public function update(){

        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $data['USU_ID'] = $_POST['id'];
            $data['USU_NOMBRE'] = $_POST['nombre'];
            $data['USU_USER'] = $_POST['user'];
            $data['USU_PASS'] = $_POST['pass'];
            $data['USU_ESTADO'] = $_POST['estado'];
            $data['USU_ACTIVO'] = $_POST['activo'];
            $data['UG_ID'] = $_POST['grupo'];

            if($this->validate($data)){

                $data = $this->getData();
                $id = $data['USU_ID'];
                unset($data['USU_ID']);

                $oUser = new User;
                $exito = $oUser->where('USU_ID', '=', $id)
                                ->update($data);
                
                $msj = "";
                if($exito){
                    $msj = 'Los datos se actulizaron exitosamente';
                }else{
                    $msj = 'Algo salio mal. No se acturizaron los datos';
                }

                $oUG = new UserGroup;
                $grupos = $oUG->select()->get();;
                View::render('pages/userUpdate', compact('exito','msj', 'grupos'));
            }else{
                $errors = $this->getErrorMessages();
                $data = (object) $this->getData();

                $oUG = new UserGroup;
                $grupos = $oUG->select()->get();;
                View::render('pages/userUpdate', compact('data','errors','grupos'));
            }

            
        }else{
            $user_id = $_GET['id']??'';
            $user_id = trim($user_id);

            if(!empty($user_id)){
                $oUser = new User;
                $data = $oUser->select()->where('USU_ID', '=', $user_id)->first();
                
                if(!empty($data)){

                    $oUG = new UserGroup;
                    $grupos = $oUG->select()->get();
                    
                    View::render('pages/userUpdate', compact('data', 'grupos'));
                    
                }else{
                    View::redirection('users/show');
                }
            }else{
                View::redirection('users/show');
            }
            
        }

    }

    public function destroy(){

        if($_SERVER['REQUEST_METHOD'] == 'POST'){

            $user_id = $_POST['id'];
            $user_id = trim($user_id);

            $oUser = new User;
            $exito = $oUser->where('USU_ID', '=', $user_id)->delete();
            
            $msj = "";
            if($exito){
                $msj = 'Usuario Eliminado';
            }else{
                $msj = 'Algo salio mal. No se elimino el usuario';
            }

            $oUG = new UserGroup;
            $grupos = $oUG->select()->get();
            View::render('pages/userDelete', compact('exito','msj', 'grupos'));
            

            
        }else{
            $user_id = $_GET['id']??'';
            $user_id = trim($user_id);

            if(!empty($user_id)){
                $oUser = new User;
                $data = $oUser->select()->where('USU_ID', '=', $user_id)->first();

                if(!empty($data)){

                    $oUG = new UserGroup;
                    $grupos = $oUG->select()->get();
                    
                    View::render('pages/userDelete', compact('data', 'grupos'));
                    
                }else{
                    View::redirection('users/show');
                }
            }else{
                View::redirection('users/show');
            }
            
        }

    }

    public function exportPdf(){
        $oUser = new User;
        $users = $oUser->join('USUARIO_GRUPO UG', 'USU.UG_ID' ,'=', 'UG.UG_ID')
                        ->select(['USU.USU_ID', 'USU.USU_NOMBRE', 'USU.USU_USER',
                                    'USU.USU_ESTADO', 'USU.USU_ACTIVO', 'UG.UG_NOMBRE'])
                        ->get();

        // Creación del objeto de la clase heredada
        $pdf = new UserPdf();
        $pdf->showPdf($users, Session::get('USU_NOMBRE'));
        
    }

    public function exportExcel(){
        $oUser = new User;
        $users = $oUser->join('USUARIO_GRUPO UG', 'USU.UG_ID' ,'=', 'UG.UG_ID')
                        ->select(['USU.USU_ID', 'USU.USU_NOMBRE', 'USU.USU_USER',
                                    'USU.USU_ESTADO', 'USU.USU_ACTIVO', 'UG.UG_NOMBRE'])
                        ->get();

        $oUserExcel = new UserExcel;
        $oUserExcel->excel($users, 'Lista de usuarios');
    }

    public function chart(){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $oUser = new User;
            $data = $oUser->select(['USU_USER', 'UG_ID'])->limit(0,10)->get();
            echo json_encode($data);
        }else{
            View::render('pages/userChart');
        }
    }
}