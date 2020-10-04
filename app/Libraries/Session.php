<?php namespace App\Libraries;
/*
 * Validar que hay una sesión activa
 */

use App\Libraries\View;

class Session {

    public static function start(){
        session_start();
    }

    public static function validate(){
        self::start();

        if(!isset($_SESSION['user']['id'])){
            View::redirection("access/login");
        }
    }

    public static function add($key, $value){
        $_SESSION[$key] = $value;
    }

    public static function get($key){
        return $_SESSION[$key] ?? null;
    }

    public static function addUserValue($key, $value){
        return $_SESSION['user'][$key] = $value;
    }

    public static function getUserValue($key){
        return $_SESSION['user'][$key] ?? null;
    }

    public static function unset($key){
        unset($_SESSION[$key]);
    }

    public static function destroy(){
        session_unset();
        session_destroy();
    }

    public static function unsetUser(){
        unset($_SESSION['user']);
    }
}