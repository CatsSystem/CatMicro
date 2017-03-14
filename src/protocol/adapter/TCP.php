<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/3/1
 * Time: 10:43
 */

namespace base\protocol\adapter;

use base\protocol\Protocol;

class TCP extends Protocol
{
    /**
     * @var \swoole_server
     */
    private $server;
    private $fd;

    public function init(\swoole_server $server, $fd)
    {
        $this->server   = $server;
        $this->fd       = $fd;
    }


    public function write($data)
    {
        $this->server->send($this->fd, pack('N', strlen($data)) . $data);
    }
}