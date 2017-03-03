<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/3/2
 * Time: 10:55
 */
namespace base\common;

class Globals
{
    /**
     * @var \swoole_server
     */
    static public $server;

    public static function isWorker()
    {
        if( empty(Globals::$server) )
        {
            return true;
        }
        return !Globals::$server->taskworker;
    }

    public static function setProcessName($name)
    {
        if(PHP_OS != 'Darwin')
        {
            swoole_set_process_name($name);
        }
    }
}