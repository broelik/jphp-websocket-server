<?php
use Throwable;
use php\http\{HttpServer, HttpServerRequest, HttpServerResponse, WebSocketSession};
use php\lib\str;
use php\util\Flow;

$users = new Users();
$users->add(new User('broelik', '123456'));
$users->add(new User('user1', '123456'));
$users->add(new User('user2', '123456'));
$clients = new Clients();

$server = new HttpServer(8888, '127.0.0.1');
$websocket = new WebSocketServer($server, '/');
$websocket->on('connect', function(WebSocketSession $session)use($clients){
    $clients->add(new Client($session));
    $session->sendText('Login please', null);
});
$websocket->on('message', function(WebSocketSession $session, $text)use($clients,$users){
    $client = $clients->find($session);
    $args = str::split($text, ' ');
    $cmd = array_shift($args);
    switch($cmd){
        case 'login':
            if($client->isLoggedIn()){
                $session->sendText("Вы уже вошли как {$client->user->login}", null);
            }
            else{
                [$login, $pass] = $args;
                $user = $users->login($login, $pass);
                if($user){
                    $client->user = $user;
                    $session->sendText("Вы вошли как {$user->login}", null);
                    $clients->sendAll("{$user->login} вошёл");
                }
                else{
                    $session->sendText('Не удалось войти', null);
                }
            }
        break;
        case 'logout':
            if($client->isLoggedIn()){
                $client->user = null;
                $session->sendText("Вы успешно вышли", null);
            }
            else{
                $session->sendText("???", null);
            }
        break;
        case 'say':
            if($client->isLoggedIn()){
                [$mess, $login] = $args;
                $success = false;
                if(empty($mess)){
                    $session->sendText("Сообщение не может быть пустым", null);
                }
                else{
                    if($login){
                        $clients = $clients->findByUser($users->get($login));
                        if($clients){
                            foreach($clients as $c){
                                $c->session->sendText("*{$client->user->login}: {$mess}", null);
                            }
                            $success = true;
                        }
                        else{
                            $session->sendText("Пользователь не найден", null);
                        }
                    }
                    else{
                        $clients->sendAll("{$client->user->login}: {$mess}");
                        $success = true;
                    }
                }
                $session->sendText($success ? 'Ваше сообщение доставлено' : 'Ошибка', null);
            }
            else{
                $session->sendText('Сначало нужно войти', null);
            }
        break;
        case 'help':
            $commands = [];
            $commands['help'] = 'Показать список команд';
            $commands['login'] = 'Войти(логин,пароль)';
            $commands['logout'] = 'Выйти';
            $commands['say'] = 'Написать всем пользователям(сообщение,[пользователь])';
            $session->sendText(Flow::of($commands)->map(function($v, $k){
                return "{$k} - {$v}";
            })->toString("\n"), null);
        break;
    }
});
$websocket->on('error', function(Throwable $e){
    var_dump($e);
});


$server->run();