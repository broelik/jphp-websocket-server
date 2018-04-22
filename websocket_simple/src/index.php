<?php 
use Throwable;
use php\http\{HttpServer, HttpServerRequest, HttpServerResponse, WebSocketSession};

$server = new HttpServer(8888, '127.0.0.1');

$handlers = [];
$handlers['onConnect'] = function(WebSocketSession $session){
    $session->sendText('Hello', null);
};
$handlers['onMessage'] = function(WebSocketSession $session, $text){
    if($text === 'bye'){
        $session->sendText('bye', null);
        $session->close(1000, 'Bye Bye');
    }
};
$handlers['onError'] = function(Throwable $e){};
$handlers['onClose'] = function(WebSocketSession $session, $status, $reason){};

$server->addWebSocket('/simple_server', $handlers);

$server->run();