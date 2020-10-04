<?php namespace App\Libraries;

use App\Controllers\PageController;

class Route {

    public static function render($controller = null, $action = 'index'){
        if($controller){
            $controller = "\\App\\Controllers\\{$controller}Controller";
            $controller = new $controller;
        }else{
            $controller = new PageController;
        }

        if(method_exists($controller, $action)){
            return $controller->$action();
        }else{
            header('HTTP/1.0 404 Not Found');
            die("Página no enconrada");
        }
    }
}