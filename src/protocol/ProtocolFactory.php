<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/3/1
 * Time: 10:40
 */

namespace base\protocol;

class ProtocolFactory
{
    public static function getInstance($type='TCP')
    {
        $type = strtoupper($type);

        $class_name = __NAMESPACE__ . '\\adapter\\' . $type;

        if( !class_exists($class_name) )
        {
            throw new \Exception("no class {$class_name}");
        }
        return new $class_name();
    }
}