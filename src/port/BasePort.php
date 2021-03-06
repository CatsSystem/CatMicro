<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/2/28
 * Time: 14:17
 */

namespace base\port;

use base\protocol\Protocol;
use base\protocol\ProtocolFactory;

abstract class BasePort
{

    /**
     * @var \swoole_server_port
     */
    protected $port;

    /**
     * @var \swoole_server
     */
    protected $server;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var Protocol
     */
    protected $protocol;


    public function init(\swoole_server $server, array $config)
    {
        $this->server = $server;
        $this->config = $config;

        $this->port = $server->listen(
            $config['host'],
            $config['port'],
            $this->getType()
        );

        /**
         * Port Define
         */
        $config = $this->handleSetting();
        $config = array_merge($config, $this->config['protocol'] ?? [] );
        if( !empty($config) )
        {
            $this->port->set($config);
        }
        return $this;
    }

    public function run()
    {
        switch (strtolower($this->config['socket_type']))
        {
            case 'tcp':
            {
                $this->port->on('Receive', array($this, 'onReceive'));
                break;
            }
            case 'http':
            {
                $this->port->on('Request', array($this, 'onRequest'));
                break;
            }
            case 'ws':
            {
                $this->port->on('Request', array($this, 'onRequest'));
                $this->port->on('Message', array($this, 'onMessage'));
                break;
            }
        }
        $this->protocol = ProtocolFactory::getInstance($this->config['socket_type']);
        $this->before_start();
    }

    /**
     * @return \swoole_server
     */
    public function getServer()
    {
        return $this->server;
    }

    private function getType()
    {
        if( !isset($this->config['enable_ssl']))
        {
            return SWOOLE_TCP;
        }

        if( $this->config['enable_ssl'] )
        {
            if( !isset($this->config['protocol']['ssl_cert_file'])
                || !isset($this->config['protocol']['ssl_key_file']) )
            {
                return SWOOLE_TCP;
            }
            else
            {
                return SWOOLE_TCP | SWOOLE_SSL;
            }
        }
        return SWOOLE_TCP;
    }

    abstract protected function handleSetting();

    abstract protected function before_start();

    abstract protected function handleProcess($data);

    /**
     * TCP Receive
     * @param \swoole_server $server
     * @param $fd
     * @param $from_id
     * @param $data
     */
    abstract public function onReceive(\swoole_server $server, $fd, $from_id, $data);

    /**
     * HTTP Receive
     * @param \swoole_http_request $request
     * @param \swoole_http_response $response
     */
    abstract public function onRequest(\swoole_http_request $request, \swoole_http_response $response);

    /**
     * WebSocket Receive
     * @param \swoole_websocket_server $server
     * @param \swoole_websocket_frame $frame
     */
    abstract public function onMessage(\swoole_websocket_server $server, \swoole_websocket_frame $frame);

}