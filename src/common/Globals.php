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
            return false;
        }
        return !Globals::$server->taskworker;
    }

}