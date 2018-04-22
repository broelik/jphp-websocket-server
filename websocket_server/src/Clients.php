<?php
use php\http\WebSocketSession;


class Clients{
    private $clients = [];
    
    
    function find(WebSocketSession $session){
        if(!$session){
            return null;
        }
        foreach($this->clients as $client){
            if($client->session === $session){
                return $client;
            }
        }
        return null;
    }
    function findByUser(User $user){
        if(!$user){
            return [];
        }
        $res = [];
        foreach($this->clients as $client){
            if($client->user === $user){
                $res[] = $client;
            }
        }
        return $res;
    }
    function get($id){
        return $this->clients[$id];
    }
    function add(Client $client){
        $this->clients[$client->id] = $client;
    }
    function sendAll($message){
        foreach($this->clients as $client){
            if($client->isLoggedIn()){
                $client->session->sendText($message, null);
            }
        }
    }
}