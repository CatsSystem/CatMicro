<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/6/15
 * Time: 上午10:48
 */

return array(
    'project'=>array(
        'pid_path'          => __DIR__ . '/../../',
        'project_name'      => 'micro_service',

        'main_callback'     => 'app\\callback\\ServerCallback',
    ),

    'base' => [
        'daemonize' => 0,

        'worker_num' => 1,
        'dispatch_mode' => 2,

        'task_worker_num' => 1,

        'package_max_length'    => 524288,
    ],

    'open_hprose'   => true,
    'open_thrift'   => true,
    'open_swoole'     => true,

    'server' => array(
        'mode' => SWOOLE_PROCESS,
        'host' => '0.0.0.0',
        'port' => 9501,
    ),
);
