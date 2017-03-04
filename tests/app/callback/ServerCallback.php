<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/3/1
 * Time: 13:45
 */

namespace app\callback;

use base\common\Globals;
use base\framework\pool\PoolManager;
use base\server\BaseCallback;

class ServerCallback extends BaseCallback
{
    /**
     * 服务启动前执行该回调, 用于添加额外监听端口, 添加额外Process
     * @return mixed
     */
    public function before_start()
    {
        // 打开内存Cache进程
        $this->open_cache_process(function(){
            PoolManager::getInstance()->init('mysql_master');
            PoolManager::getInstance()->init('redis_master');
        });
    }

    /**
     * 进程初始化回调, 用于初始化全局变量
     * @param \swoole_websocket_server $server
     * @param $workerId
     */
    public function onWorkerStart($server, $workerId)
    {
        Globals::$open_task = true;
        PoolManager::getInstance()->init('mysql_master');
        PoolManager::getInstance()->init('redis_master');
    }

    /**
     * Admin 管理接口, 可自定义管理接口行为
     * @param \swoole_http_request $request
     * @param \swoole_http_response $response
     */
    public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {

    }
}