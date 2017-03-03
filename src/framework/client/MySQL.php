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
    private $config;

    public $id;

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
        if( !empty($this->pool) )
        {
            $this->pool->push($this, true);
        }
    }

    public function query()
    {

    }
}