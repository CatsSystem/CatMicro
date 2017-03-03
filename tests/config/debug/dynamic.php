<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/2/27
 * Time: 13:45
 */

return [
    /*********************** Log Config ****************************/
    'log'=>array(
        'open_log' => true,
        'adapter' => 'Debug',
        'log_level' => 1,
    ),
    /*********************** Log Config end ************************/

    /*********************** Pool Config Start ***********************/

    'pool'  => [
        // 同一类型的连接池不限个数
        [
            'type'  => 'mysql',
            'name'  => 'mysql_master',
            'size'  => 5,

            'args'  => [
                'host'      => '127.0.0.1',
                'port'      => 3306,
                'user'      => 'root',
                'password'  => '123456',
                'database'  => 'Test'
            ]
        ],

        [
            'type'  => 'redis',
            'name'  => 'redis_master',

            'args'  => [
                'host'      => '127.0.0.1',
                'port'      => 6379,
                'select'    => 0,
                'pwd'       => '123456'
            ]
        ],
    ]
    /*********************** Pool Config end *************************/
];