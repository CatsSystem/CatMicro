<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/4/8
 * Time: 下午5:43
 */

namespace base\server;

use base\common\Constants;
use base\common\Globals;
use base\concurrent\Promise;
use base\framework\config\Config;
use base\framework\cache\CacheLoader;
use base\framework\task\TaskRoute;
use base\port\PortFactory;

abstract class BaseCallback
{
    /**
     * @var \swoole_server
     */
    protected $server;

    protected $project_name;

    protected $pid_path;

    public function __construct()
    {
        $this->project_name = Config::getField('project', 'project_name');
        $this->pid_path   = Config::getField('project', 'pid_path');
    }

    public function onStart($server)
    {
        Globals::setProcessName($this->project_name . " server running master:" . $server->master_pid);
        if (!empty($this->pid_path)) {
            file_put_contents($this->pid_path . DIRECTORY_SEPARATOR . $this->project_name . '_master.pid', $server->master_pid);
        }
    }

    /**
     * @throws \Exception
     */
    public function onShutDown()
    {
        if (!empty($this->pid_path)) {
            $filename = $this->pid_path . DIRECTORY_SEPARATOR . $this->project_name . '_master.pid';
            if (is_file($filename)) {
                unlink($filename);
            }
            $filename = $this->pid_path . DIRECTORY_SEPARATOR . $this->project_name . '_manager.pid';
            if (is_file($filename)) {
                unlink($filename);
            }
        }
    }

    /**
     * @param $server
     * @throws \Exception
     * @desc 服务启动，设置进程名
     */
    public function onManagerStart($server)
    {
        Globals::setProcessName($this->project_name .' server manager:' . $server->manager_pid);
        if (!empty($this->pid_path)) {
            file_put_contents($this->pid_path . DIRECTORY_SEPARATOR . $this->project_name . '_manager.pid', $server->manager_pid);
        }
    }

    public function onManagerStop()
    {
        if (!empty($this->pid_path)) {
            $filename = $this->pid_path . DIRECTORY_SEPARATOR . $this->project_name . '_manager.pid';
            if (is_file($filename)) {
                unlink($filename);
            }
        }
    }

    public function doWorkerStart($server, $workerId)
    {
        $workNum = Config::getField('base', 'worker_num');
        if ($workerId >= $workNum) {
            Globals::setProcessName($this->project_name . " server tasker  num: ".($server->worker_id - $workNum)." pid " . $server->worker_pid);
        } else {
            Globals::setProcessName($this->project_name . " server worker  num: {$server->worker_id} pid " . $server->worker_pid);
        }
        Globals::$server = $server;

        $this->onWorkerStart($server, $workerId);
    }

    public function setServer(\swoole_server $server)
    {
        $this->server = $server;
    }

    /**
     * @return \swoole_server
     */
    public function getServer()
    {
        return $this->server;
    }

    public function onTask(\swoole_server $server, $task_id, $from_id, $data)
    {
        Promise::co(function () use ($server, $data) {
            $result = TaskRoute::route($data);
            $server->finish($result);
        });
    }

    public function onFinish(\swoole_server $serv, $task_id, $data)
    {
        // DO NOTHING
    }

    public function onPipeMessage(\swoole_server $server, $from_worker_id, $message)
    {
        $data = json_decode($message, true);
        if( $data['type'] == 'cache' )
        {
            CacheLoader::getInstance()->set($data['id'], $data['data']);
        }
        return;
    }

    public function _before_start()
    {
        $service_list = Config::get('service');
        foreach ($service_list as $service)
        {
            $switch = 'open_' . $service['port_type'];

            if( !Config::get($switch, false) ) {
                continue;
            }
            $port = PortFactory::getInstance($service['port_type']);
            $port->init($this->server, $service);
            $port->run();
        }

        $this->before_start();
    }

    /**
     * 打开内存Cache进程
     * @param $init_callback callable 回调函数, 执行进程的初始化代码
     */
    protected function open_cache_process($init_callback)
    {
        Globals::$open_cache = true;
        if( Globals::isOpenCache() )
        {
            $process = new \swoole_process(function(\swoole_process $worker) use ($init_callback) {
                // $worker->name(Config::get('project_name') . " cache process");
                Globals::setProcessName(Config::get('project_name') . " cache process");
                CacheLoader::getInstance()->init();
                if( is_callable($init_callback) )
                {
                    call_user_func($init_callback);
                }
                CacheLoader::getInstance()->load(true);
                swoole_timer_tick(Constants::ONE_TICK, function(){
                    CacheLoader::getInstance()->load();
                });
            }, false, false);
            $this->server->addProcess($process);
        }
    }

    /**
     * 服务启动前执行该回调, 用于添加额外监听端口, 添加额外Process
     */
    abstract public function before_start();

    /**
     * Admin 管理接口, 可自定义管理接口行为
     * @param \swoole_http_request $request
     * @param \swoole_http_response $response
     */
    abstract public function onRequest(\swoole_http_request $request, \swoole_http_response $response);


    /**
     * 进程初始化回调, 用于初始化全局变量
     * @param \swoole_websocket_server $server
     * @param $workerId
     */
    abstract public function onWorkerStart($server, $workerId);


    /**
     * WebSocket Receive
     * @param \swoole_websocket_server $server
     * @param \swoole_websocket_frame $frame
     */
    public function onMessage(\swoole_websocket_server $server, \swoole_websocket_frame $frame)
    {
        // DO NOTHING
    }

}
