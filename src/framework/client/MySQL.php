<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/3/3
 * Time: 10:52
 */

namespace base\framework\client;

use base\common\Error;
use base\concurrent\Promise;
use base\framework\log\Log;
use base\framework\pool\BasePool;

class MySQL
{
    public $id;

    /**
     * 配置选项
     * @var array
     */
    private $config;

    /**
     * @var \swoole_mysql
     */
    private $db;

    /**
     * @var BasePool
     */
    private $pool;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function addPool($pool)
    {
        $this->pool = $pool;
    }

    public function connect($id, $timeout=3000)
    {
        $this->id = $id;
        $promise = new Promise();

        $this->db = new \swoole_mysql();
        $this->db->on('Close', function($db){
            $this->close();
        });
        $timeId = swoole_timer_after($timeout, function() use ($promise){
            $this->close();
            $promise->reject(Error::ERR_MYSQL_TIMEOUT);
        });
        $this->db->connect($this->config, function($db, $r) use ($promise,$timeId) {
            swoole_timer_clear($timeId);
            if ($r === false) {
                Log::ERROR('MySQL' , sprintf("Connect MySQL Failed [%d]: %s", $db->connect_errno, $db->connect_error));
                $promise->reject(Error::ERR_MYSQL_CONNECT_FAILED);
                return;
            }
            $promise->resolve(Error::SUCCESS);
        });
        return $promise;
    }

    public function close()
    {
        $this->db->close();
        unset($this->db);
        $this->inPool(true);
    }

    private function inPool($close = false)
    {
        if( !empty($this->pool) )
        {
            $this->pool->push($this, $close);
        }
    }

    public function execute($sql, $timeout)
    {
        $promise = new Promise();

        $timeId = swoole_timer_after($timeout, function() use ($promise, $sql){
            $this->inPool();
            $promise->resolve([
                'code' => Error::ERR_MYSQL_TIMEOUT,
            ]);
        });
        $this->db->query($sql, function($db, $result) use ($sql, $promise, $timeId){
            $this->inPool();
            swoole_timer_clear($timeId);
            if($result === false) {
                Log::ERROR('MySQL', sprintf("%s \n [%d] %s",$sql, $db->errno, $db->error));
                $promise->resolve([
                    'code'  => Error::ERR_MYSQL_QUERY_FAILED,
                    'errno' => $db->errno,
                    'msg'   => sprintf("%s \n [%d] %s",$sql, $db->errno, $db->error)
                ]);
            } else if($result === true) {
                $promise->resolve([
                    'code'          => Error::SUCCESS,
                    'affected_rows' => $db->affected_rows,
                    'insert_id'     => $db->insert_id
                ]);
            } else {
                $promise->resolve([
                    'code'  => Error::SUCCESS,
                    'data'  => empty($result) ? [] : $result
                ]);
            }
        });
        return $promise;
    }
}