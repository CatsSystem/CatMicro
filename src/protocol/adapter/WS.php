<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/3/1
 * Time: 10:43
 */

namespace base\protocol\adapter;

use base\protocol\Protocol;

class WS extends Protocol
{
    /**
     * @var \swoole_websocket_server
     */
    private $server;

    private $fd;

    public function init(\swoole_websocket_server $server, $fd)
    {
        $this->server = $server;
        $this->fd = $fd;
    }

    public function write($data)
    {
        $this->server->push($this->fd, $data);
    }
}