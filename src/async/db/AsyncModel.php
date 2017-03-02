<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/10/26
 * Time: 下午6:25
 */

namespace base\async\db;

use base\concurrent\Promise;

class AsyncModel
{
    private $table;

    public function __construct($table)
    {
        $this->table = $table;
    }

    public function query($sql, $timeout = 3000)
    {
        $promise = new Promise();
        $driver = Pool::getInstance()->get($sql, $promise, $timeout);
        if(empty($driver))
        {
            return $promise;
        }
        $driver->async_query($sql, $promise, $timeout);
        return $promise;
    }
}