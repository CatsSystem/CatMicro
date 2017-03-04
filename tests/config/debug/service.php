<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/3/2
 * Time: 18:51
 */

/**
 * 服务自定义配置
 */
return [

    'open_hprose'   => true,
    'open_thrift'   => true,
    'open_swoole'     => true,

    'service' => [
        [
            'port_type'         => 'hprose',                            // 模块类型
            'socket_type'       => 'tcp',                               // tcp, http, ws,
            'enable_ssl'        => false,                               // 是否开启SSL
            'host'              => '0.0.0.0',
            'port'              => 9502,

            'processor_path'    => 'app\\processor\\HproseService',     // Processor路径
            'service_path'      => 'app\\service\\HproseService',       // Service路径

            'protocol'  => [    // 自定义协议设置

            ]
        ],
        [
            'port_type'         => 'thrift',
            'socket_type'       => 'tcp',
            'enable_ssl'        => false,
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
            'enable_ssl'        => false,
            'host'              => '0.0.0.0',
            'port'              => 9504,

            'processor_path'    => 'app\\processor\\SwooleService',
            'service_path'      => 'app\\service\\SwooleService',

            'protocol'  => [

            ]
        ]
    ]
];