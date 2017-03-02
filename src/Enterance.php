<?php

namespace base;

use base\common\Formater;
use base\server\MainServer;
use base\socket\SwooleServer;
use base\config\Config;

class Enterance
{
    public static $rootPath;
    public static $configPath;

    final public static function fatalHandler()
    {
        $error = \error_get_last();
        if(empty($error)) {
            return '';
        }
        if(!in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR))) {
            return '';
        }

        return json_encode(Formater::fatal($error));
    }

    public static function checkLib()
    {
        if(!\extension_loaded('swoole')) {
            throw new \Exception("no swoole extension. get: https://github.com/swoole/swoole-src");
        }

        if(!\extension_loaded('swoole_serialize')) {
            throw new \Exception("no swoole_serialize extension. get: https://github.com/swoole/swoole_serialize");
        }

        if( Config::get('open_hprose', false) && !\extension_loaded('hprose')) {
            throw new \Exception("no open_hprose extension. get: https://github.com/hprose/hprose-pecl");
        }

        if( Config::get('open_thrift', false) && !\extension_loaded('thrift_protocol')) {
            throw new \Exception("no open_thrift extension. get: https://github.com/apache/thrift");
        }
    }

    public static function run($runPath, $configPath)
    {
        self::$rootPath = $runPath;
        self::$configPath = $runPath . '/config/' . $configPath;

        Config::load(self::$configPath);

        self::checkLib();

        \register_shutdown_function( __CLASS__ . '::fatalHandler' );

        $timeZone = Config::get('time_zone', 'Asia/Shanghai');
        \date_default_timezone_set($timeZone);

        $service = MainServer::getInstance()->init(Config::get('server'));
        $callback = Config::getField('project', 'main_callback');
        if( !class_exists($callback) )
        {
            throw new \Exception("No class {$callback}");
        }
        $service->setCallback(new $callback());
        $service->run();
    }
}

