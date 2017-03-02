<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/3/2
 * Time: 18:51
 */

return [
    'service' => [
        [
            'port_type'         => 'hprose',
            'socket_type'       => 'tcp',
            'host'              => '0.0.0.0',
            'port'              => 9502,

            'processor_path'    => 'app\\processor\\HproseService',
            'service_path'      => 'app\\service\\HproseService',

            'protocol'  => [

            ]
        ],
        [
            'port_type'         => 'thrift',
            'socket_type'       => 'tcp',
            'host'              => '0.0.0.0',
            'port'              => 9503,

            'processor_path'    => 'app\\processor\\ThriftService',
            'service_path'      => 'app\\service\\ThriftService',

            'protocol'  => [

            ]
        ],
        [
            'port_type'         => 'swoole',
            'socket_type'       => 'http',
            'host'              => '0.0.0.0',
            'port'              => 9504,

            'processor_path'    => 'app\\processor\\SwooleService',
            'service_path'      => 'app\\service\\SwooleService',

            'protocol'  => [

            ]
        ]
    ]
];