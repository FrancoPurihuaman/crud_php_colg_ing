<?php

require_once __DIR__.'/../config/app.php';

use App\Libraries\Route;
use App\Libraries\Session;

$url = $_GET['url'] ?? '';
$route = ROUTES[$url] ?? false;

if($route){
    $controller = $route['controller'];
    $action = $route['action'];
    
    if($controller == 'Page'){
        Route::render($controller, $action);
    }elseif($controller == 'Access' && $action == 'login'){
        Route::render($controller, $action);
    }else{
        Session::validate();
        Route::render($controller, $action);
    }
}else{
    header('HTTP/1.0 404 Not Found');
    die("Página no enconrada");
}