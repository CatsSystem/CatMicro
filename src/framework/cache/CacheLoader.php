<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/12/2
 * Time: 下午9:53
 */
namespace base\framework\cache;

use base\common\Error;
use base\Entrance;
use base\concurrent\Promise;
use base\framework\config\Config;

class CacheLoader
{
    private static $instance = null;

    /**
     * @return CacheLoader
     */
    public static function getInstance()
    {
        if(CacheLoader::$instance == null)
        {
            CacheLoader::$instance = new CacheLoader();
        }
        return CacheLoader::$instance;
    }
    
    public function __construct()
    {
    
    }
    /**
     * @var array(ILoader)
     */
    private $loaders = [];

    public function init()
    {
        $cache_file_path = Config::getSubField('component', 'cache', 'cache_path');

        if( !file_exists(Entrance::$rootPath . $cache_file_path) )
        {
            return;
        }
        $files = new \DirectoryIterator(Entrance::$rootPath . $cache_file_path);
        foreach ($files as $file) {
            $filename = $file->getFilename();
            if ($filename[0] === '.') {
                continue;
            }
            if (!$file->isDir()) {
                $loader = substr($filename, 0, strpos($filename, '.'));
                $class_name = str_replace('/','\\',$cache_file_path) . $loader;
                $ob = new $class_name();
                if( ! $ob instanceof ILoader ) {
                    continue;
                }
                $this->loaders[$ob->getId()] = $ob;
            }
        }
    }

    public function load($force=false)
    {
        foreach ($this->loaders as $loader)
        {
            if( $force || $loader->refresh() ) {
                $promise = new Promise();
                $promise->then(function($value) use ($loader){
                    if( $value['code'] == Error::SUCCESS )
                    {
                        $loader->broadcast($value['data']);
                    }
                });
                Promise::co(function() use ($loader, &$promise){
                    $loader->load($promise);
                });

            }
        }
    }

    public function set($id, $data)
    {
        $this->loaders[$id]->set($data);
    }

    public function get($id)
    {
        return $this->loaders[$id]->get();
    }
}