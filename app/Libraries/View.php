<?php namespace App\Libraries;

class View {

    public static function render($fileView, array $variables = []){
        extract($variables);
        require_once APP_PATH.'/resources/views/'.$fileView.'.view.php';
    }
    
    public static function redirection($ruta){
        $redirection = "Location: ".PUBLIC_PATH."/{$ruta}";
        header($redirection);
    }
}
