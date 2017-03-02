<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/4/8
 * Time: 下午5:43
 */

namespace base\server;

use base\common\Globals;
use base\config\Config;
use base\framework\cache\CacheLoader;
use base\framework\task\Task;
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

        swoole_set_process_name($this->project_name . " server running master:" . $server->master_pid);
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
        swoole_set_process_name($this->project_name .' server manager:' . $server->manager_pid);
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

    public function onWorkerStart($server, $workerId)
    {
        $workNum = Config::getField('server', 'worker_num');
        if ($workerId >= $workNum) {
            swoole_set_process_name($this->project_name . " server tasker  num: ".($server->worker_id - $workNum)." pid " . $server->worker_pid);
        } else {
            swoole_set_process_name($this->project_name . " server worker  num: {$server->worker_id} pid " . $server->worker_pid);
        }
        Globals::$server = $server;
    }

    public function onWorkerStop($server, $workerId)
    {
        
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
        $task = new Task($data);
        $result = TaskRoute::route($task);
        return $result;
    }

    public function onFinish(\swoole_server $serv, $task_id, $data)
    {

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
        $port_list = Config::get('port');
        foreach ($port_list as $name => $port_config)
        {
            $switch = 'open_' . $name;

            if( !Config::get($switch, false) ) {
                continue;
            }
            $port = PortFactory::getInstance($name);
            $port->init($this->server, $port_config);
            $port->run();
        }
        $this->before_start();
    }

    /**
     * run before server start
     * @return mixed
     */
    abstract public function before_start();

    /**
     * HTTP Receive
     * @param \swoole_http_request $request
     * @param \swoole_http_response $response
     */
    abstract public function onRequest(\swoole_http_request $request, \swoole_http_response $response);

    /**
     * WebSocket Receive
     * @param \swoole_websocket_server $server
     * @param \swoole_websocket_frame $frame
     */
    abstract public function onMessage(\swoole_websocket_server $server, \swoole_websocket_frame $frame);

}
