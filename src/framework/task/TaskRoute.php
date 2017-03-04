<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/12/6
 * Time: 下午11:09
 */
namespace base\framework\task;

use base\common\Factory;
use base\framework\config\Config;

class TaskRoute
{
    public static function route($data)
    {
        try {
            $data = swoole_unpack($data);
            $action = $cache_file_path = Config::getSubField('component', 'task', 'task_path') . $data['task'];
            $action = str_replace('/','\\',$action);
            if (!\class_exists($action)) {
                throw new \Exception("no class {$action}");
            }
            $class = new $action();

            if (!($class instanceof IRunner)) {
                throw new \Exception("task error");
            } else {
                $method = $data['method'];
                if (!method_exists($class, $method)) {
                    throw new \Exception("method error");
                }

                $result = call_user_func_array([$class,$method], $data['params']);
                return swoole_pack($result);
            }
        }catch (\Exception $e) {
            $result = var_export($e);
            if( !Config::get('debug', false) )
            {
                $result = "Error in Server";
            }
            return $result;
        }
    }
}