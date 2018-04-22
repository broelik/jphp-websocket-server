<?php
use Throwable;
use php\http\{HttpServer, HttpServerRequest, HttpServerResponse, WebSocketSession};
use php\lib\str;

class WebSocketServer{
    /**
    * @var HttpServer
    */
    private $server;
    private $handlers = [];
    private $path;
    
    
    function __construct(HttpServer $server, $path){
        $this->server = $server;
        $this->path = $path;
        $this->initSocket();
    }
    private function initSocket(){
        $handlerSet = ['onConnect' => 'connect', 'onClose' => 'close', 'onMessage' => 'message', 'onError' => 'error', 'onBinaryMessage' => 'binaryMessage'];
        $handlers = [];
        foreach($handlerSet as $handler => $event){
            $handlers[$handler] = function()use($event){
                $this->trigger($event, ...func_get_args());
            };
        }
        $this->server->addWebSocket($this->path, $handlers);
    }
    function on($event, callable $handler, $group = null){
        $group = $group ?? str::uuid();
        if(!$this->handlers[$event]){
            $this->handlers[$event] = [];
        }
        $this->handlers[$event][$group] = $handler;
    }
    function off($event, $group = null){
        if(!$this->handlers[$event]){
            return;
        }
        if($group){
            unset($this->handlers[$event][$group]);
        }
        else{
            $this->handlers[$event] = [];
        }
    }
    private function trigger($event, ...$args){
        if(!$this->handlers[$event]){
            return;
        }
        foreach($this->handlers[$event] as $handler){
            try{
                !is_callable($handler) ?: $handler(...$args); 
            }
            catch(Throwable $e){
                //nop
            }
        }
    }
}