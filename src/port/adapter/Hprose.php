<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/2/28
 * Time: 14:26
 */
namespace base\port\adapter;

use base\port\BasePort;
use Hprose\Swoole\Http\Service as HttpService;
use Hprose\Swoole\Socket\Service as SocketService;
use Hprose\Swoole\WebSocket\Service as WSService;

class Hprose extends BasePort
{

    protected $service;

    protected function handleSetting()
    {
        return [
            'open_eof_check' => false
        ];
    }


    protected function before_start()
    {
        switch (strtolower($this->config['socket_type']))
        {
            case 'tcp':
            {
                $this->service = new SocketService();
                $this->service->socketHandle($this->port);
                break;
            }
            case 'http':
            case 'https':
            {
                $this->service = new HttpService();
                $this->service->httpHandle($this->port);
                break;
            }
            case 'ws':
            case 'wss':
            {
                $this->service = new WSService();
                $this->service->wsHandle($this->port);
                break;
            }
        }
        $handler_class = $this->config['service_path'];
        $handler = new $handler_class();
        $this->service->errorTypes = E_ALL;
        $this->service->add($handler);
    }

    protected function handleProcess($data)
    {
        
    }

    /**
     * TCP Receive
     * @param \swoole_server $server
     * @param $fd
     * @param $from_id
     * @param $data
     */
    public function onReceive(\swoole_server $server, $fd, $from_id, $data)
    {
        // TODO: Implement onReceive() method.
        var_dump($data);
    }
    

    /**
     * HTTP Receive
     * @param \swoole_http_request $request
     * @param \swoole_http_response $response
     */
    public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {
        // TODO: Implement onRequest() method.
    }

    /**
     * WebSocket Receive
     * @param \swoole_websocket_server $server
     * @param \swoole_websocket_frame $frame
     */
    public function onMessage(\swoole_websocket_server $server, \swoole_websocket_frame $frame)
    {
        // TODO: Implement onMessage() method.
    }
}