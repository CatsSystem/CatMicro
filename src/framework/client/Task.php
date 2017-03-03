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

class Task
{
    /**
     * @param $task     string
     * @param $method   string
     * @param $param    mixed
     * @return Promise
     * @throws \Exception
     */
    public static function task($task, $method, $param)
    {
        if(!Globals::isWorker())
        {
            throw new \Exception("Can not use task in Task Worker");
        }
        $promise = new Promise();
        $data = swoole_pack([
            'task'  => $task,
            'method'=> $method,
            'params'  => $param
        ]);
        Globals::$server->task($data, -1, function(\swoole_server $serv, $task_id, $data) use ($promise) {
            $promise->resolve($data);
        });
        return $promise;
    }

}

