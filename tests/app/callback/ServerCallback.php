<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/3/1
 * Time: 13:45
 */

namespace app\callback;

use base\async\db\Pool;
use base\common\Globals;
use base\server\BaseCallback;

class ServerCallback extends BaseCallback
{
    /**
     * run before server start
     * @return mixed
     */
    public function before_start()
    {

    }

    public function onWorkerStart($server, $workerId)
    {
        parent::onWorkerStart($server, $workerId);
        if( Globals::isWorker() )
        {
            Pool::getInstance()->init();
        }

    }

    /**
     * HTTP Receive
     * @param \swoole_http_request $request
     * @param \swoole_http_response $response
     */
    public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {

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