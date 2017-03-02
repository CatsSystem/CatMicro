<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/3/1
 * Time: 10:43
 */

namespace base\protocol\adapter;

use base\protocol\Protocol;

class HTTP extends Protocol
{
    /**
     * @var \swoole_http_response
     */
    private $response;

    public function init(\swoole_http_response $response)
    {
        $this->response = $response;
    }

    public function write($data)
    {
        $this->response->end($data);
    }
}