<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/3/2
 * Time: 17:34
 */

namespace base\async\task;

use base\common\Globals;
use base\concurrent\Promise;

class AsyncTask
{
    /**
     * @param $task     string
     * @param $method   string
     * @param $param    mixed
     * @return Promise
     */
    public static function task($task, $method, $param)
    {
        $promise = new Promise();
        $data = json_encode([
            'task'  => $task,
            'method'=> $method,
            'params'  => $param
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        Globals::$server->task($data, -1, function(\swoole_server $serv, $task_id, $data) use ($promise) {
            $promise->resolve($data);
        });
        return $promise;
    }

}

