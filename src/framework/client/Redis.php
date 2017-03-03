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
use base\framework\pool\BasePool;

class Redis
{

    public $id;

    /**
     * @var \swoole_redis
     */
    private $db;

    /**
     * @var int
     */
    private $timeout = 3000;

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

    private function inPool($close = false)
    {
        if( !empty($this->pool) )
        {
            $this->pool->push($this, $close);
        }
    }

    public function close()
    {
        $this->db->close();
        unset($this->db);
        $this->inPool(true);
    }

    public function connect($timeout = 3000)
    {
        $promise = new Promise();
        $this->db = new \swoole_redis();

        $this->db->on("close", function(){
            $this->connect();
        });
        $timeId = swoole_timer_after($timeout, function() use ($promise){
            $this->close();
            $promise->resolve([
                'code'  => Error::ERR_REDIS_TIMEOUT
            ]);
        });
        $this->db->connect($this->config['host'], $this->config['port'],
            function (\swoole_redis $client, $result) use($timeId,$promise){
                \swoole_timer_clear($timeId);
                if( $result === false ) {
                    $promise->resolve([
                        'code'      => Error::ERR_REDIS_CONNECT_FAILED,
                        'errCode'   => $client->errCode,
                        'errMsg'    => $client->errMsg,
                    ]);
                    return;
                }
                if( isset($this->config['pwd']) ) {
                    $client->auth($this->config['pwd'], function(\swoole_redis $client, $result) use ($promise){
                        if( $result === false ) {
                            $this->close();
                            $promise->resolve([
                                'code'  => Error::ERR_REDIS_ERROR,
                                'errCode'   => $client->errCode,
                                'errMsg'    => $client->errMsg,
                            ]);
                            return;
                        }
                        $client->select($this->config['select'], function(\swoole_redis $client, $result){});
                        $promise->resolve([
                            'code'  => Error::SUCCESS
                        ]);
                    });
                } else {
                    $client->select($this->config['select'], function(\swoole_redis $client, $result){});
                    $promise->resolve([
                        'code'  => Error::SUCCESS
                    ]);
                }
            });
        return $promise;
    }

    public function __call($name, $arguments)
    {
        $promise = new Promise();
        if( $name == 'subscribe' || $name == 'unsubscribe'
            || $name == 'psubscribe' || $name == 'punsubscribe' ) {

        } else {
            $index = count($arguments);
            $timeId = swoole_timer_after($this->timeout, function() use ($promise){
                $this->close();
                $promise->resolve([
                    'code'  => Error::ERR_REDIS_TIMEOUT
                ]);
            });
            $arguments[$index] = function (\swoole_redis $client, $result) use ($timeId, $promise){
                \swoole_timer_clear($timeId);
                if( $result === false )
                {
                    $promise->resolve([
                        'code'      => Error::ERR_REDIS_ERROR,
                        'errCode'   => $client->errCode,
                        'errMsg'    => $client->errMsg,
                    ]);
                    return;
                }
                $promise->resolve([
                    'code'  => Error::SUCCESS,
                    'data'  => $result
                ]);
            };
        }
        call_user_func_array([$this->db, $name], $arguments);
        return $promise;
    }

    /**
     * @param mixed $timeout
     * @return Redis
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }
}

