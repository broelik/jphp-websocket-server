<?php
use php\http\WebSocketSession;

class User{
    public $login;
    public $password;
    
    function __construct($login, $password){
        $this->login = $login;
        $this->password = $password;
    }
}