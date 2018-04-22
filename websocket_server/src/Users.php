<?php
use php\lib\str;

class Users{
    private $users = [];
    
    
    function get($login){
        return $this->users[$login];
    }
    function login($login, $pass){
        if(!$login || !$pass){
            return null;
        }
        $user = $this->get($login);
        return ($user && $user->password === $pass) ? $user : null;
    }
    function add(User $user){
        $this->users[$user->login] = $user;
    }
}