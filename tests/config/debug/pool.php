<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/2/27
 * Time: 13:45
 */


/**
 * 连接池配置
 */
return [
    /*********************** Pool Config Start ***********************/

    'pool'  => [
        /**
         * MySQL 连接池
         */
        [
            'type'  => 'mysql',                 // 连接池类型
            'name'  => 'mysql_master',          // 连接池名称(唯一标识)
            'size'  => 5,                       // 连接池大小

            'args'  => [                        // 连接参数
                'host'      => '127.0.0.1',     // 主机名
                'port'      => 3306,            // 端口号
                'user'      => 'root',          // 用户名
                'password'  => '123456',        // 密码
                'database'  => 'Test'           // 数据库名称
            ]
        ],

        /**
         * Redis 连接池
         */
        [
            'type'  => 'redis',                 // 连接池类型
            'name'  => 'redis_master',          // 连接池名称(唯一标识)
            // 'size' => 1,                     // 默认为 1 连接, 无需设置

            'args'  => [
                'host'      => '127.0.0.1',     // 主机名
                'port'      => 6379,            // 端口号
                'select'    => 0,               // 库编号
                'pwd'       => '123456'         // 口令
            ]
        ],
    ]
    /*********************** Pool Config end *************************/
];