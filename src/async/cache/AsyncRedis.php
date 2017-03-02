<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/10/28
 * Time: 下午5:46
 */

namespace base\async\cache;

use base\concurrent\Promise;
use base\framework\config\Config;
use base\common\Error;

class AsyncRedis
{
    private static $instance = null;

    /**
     * @param array $config
     * @return AsyncRedis
     */
    public static function getInstance($config = [])
    {
        if(AsyncRedis::$instance == null)
        {
            AsyncRedis::$instance = new AsyncRedis($config);
        }
        return AsyncRedis::$instance;
    }

    /**
     * @var \swoole_redis
     */
    private $redis;

    private $config;

    private $timeout = 3000;

    public function __construct($config)
    {
        $this->config = $config;
        if( empty($this->config) ) {
            $this->config = Config::get('redis');
        }

        //TODO Cluster
    }

    public function connect($timeout = 3000)
    {
        $promise = new Promise();
        $this->redis = new \swoole_redis();

        $this->redis->on("close", function(){
            $this->connect();
        });
        $timeId = swoole_timer_after($timeout, function() use ($promise){
            $this->close();
            $promise->resolve([
                'code'  => Error::ERR_REDIS_TIMEOUT
            ]);
        });
        $this->redis->connect($this->config['host'], $this->config['port'],
            function (\swoole_redis $client, $result) use($timeId,$promise){
                \swoole_timer_clear($timeId);
                if( $result ) {
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
                } else {
                    $promise->resolve([
                        'code'      => Error::ERR_REDIS_CONNECT_FAILED,
                        'errCode'   => $client->errCode,
                        'errMsg'    => $client->errMsg,
                    ]);
                    return;
                }
        });
        return $promise;
    }

    public function close()
    {
        $this->redis->close();
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
        call_user_func_array([$this->redis, $name], $arguments);
        return $promise;
    }

    /**
     * @param mixed $timeout
     * @return AsyncRedis
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

}