<?php namespace App\Controllers;

use App\Libraries\View;
use App\Libraries\Session;

class PanelController {

    public function index(){
        var_dump($_SESSION['user']);
        View::render('pages/panelPrincipal');
    }

    public function goOut(){
        Session::destroy();
        View::redirection('');
    }
}
