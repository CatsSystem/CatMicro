<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/3/1
 * Time: 10:40
 */

namespace base\port;

class PortFactory
{
    /**
     * @param string $type
     * @return BasePort
     * @throws \Exception
     */
    public static function getInstance($type='json')
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