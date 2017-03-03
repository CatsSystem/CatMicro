<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/3/3
 * Time: 12:57
 */

namespace base\framework\pool;

use base\framework\config\Config;

class PoolManager
{
    private static $instance = null;

    /**
     * @return PoolManager
     */
    public static function getInstance()
    {
        if(PoolManager::$instance == null)
        {
            PoolManager::$instance = new PoolManager();
        }
        return PoolManager::$instance;
    }

    private $config;

    /**
     * @var array[BasePool]
     */
    private $pools = [];
    
    protected function __construct()
    {
        $config = Config::get('pool');

        foreach ($config as $pool)
        {
            $this->config[$pool['name']] = $pool;
        }
    }

    public function init($name)
    {
        if( !in_array($name, $this->config) )
        {
            return;
        }
        if( !isset($this->pools[$name]) )
        {
            $this->pools[$name] = PoolFactory::getInstance($name);
            $this->pools[$name]->init();
        }
    }

    public function get($name)
    {
        return $this->pools[$name] ?? null;
    }


}