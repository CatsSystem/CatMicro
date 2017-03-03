<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/3/3
 * Time: 17:31
 */
namespace base\framework\pool;

class PoolFactory
{
    /**
     * @param string $type
     * @return BasePool
     * @throws \Exception
     */
    public static function getInstance($type='mysql')
    {
        $type = ucfirst(strtolower($type));

        $class_name = __NAMESPACE__ . '\\adapter\\' . $type;

        if( !class_exists($class_name) )
        {
            throw new \Exception("no class {$class_name}");
        }
        return new $class_name();
    }
}