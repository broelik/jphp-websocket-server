<?php

use php\lib\str;

class Client{
    public $session;
    public $id;
    public $user;
    
    
    function __construct($session){
        $this->session = $session;
        $this->id = str::uuid();
    }
    function isLoggedIn(){
        return isset($this->user);
    }
}