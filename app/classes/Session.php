<?php

namespace App;

class Session
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function add(string $key, $data){
    $_SESSION[$key]=$data;}

    
    public function get(string $key) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }
    
        public function isConnected(){
        return isset ($_SESSION['user']);
    }
    public function asRole($role){
        return $_SESSION['user']['role']== $role ? true : false;
    }
    public function logout()
    {
        unset($_SESSION);
        session_destroy();
        header('Location: index.php');
    }
    public function startUserSession($userId, $role) {
        $_SESSION['user_id'] = $userId;
        $_SESSION['role'] = $role;
    }

}
