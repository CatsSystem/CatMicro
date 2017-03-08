<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/4/8
 * Time: 下午4:29
 */

namespace base\port\adapter;

use core\common\Error;
use core\common\Formater;
use core\concurrent\Promise;
use core\framework\log\Log;
use base\port\BasePort;

class Swoole extends BasePort
{
    protected function handleSetting()
    {
        // TCP 开启包长检测
        if(strtolower($this->config['socket_type']) == 'tcp')
        {
            $config["open_length_check"]      = true;
            $config["package_length_type"]    = 'N';
            $config["package_length_offset"]  = 0;
            $config["package_body_offset"]    = 4;
            return $config;
        }
        return [];
    }
    
    protected function before_start()
    {
        // TODO: Implement before_start() method.
    }

    protected function handleProcess($data)
    {
        Promise::co(function() use($data){
            $data = swoole_unpack($data);
            if( empty($data) )
            {
                $this->protocol->write(swoole_pack([
                    'code'  => Error::ERR_INVALID_DATA,
                    'msg'   => 'Invalid Data'
                ]));
                return;
            }

            $method     = $data['method'];

            $handler_class = $this->config['service_path'];
            $handler = new $handler_class();
            try {
                $response = yield call_user_func_array([$handler, $method], $data['data']);
                $this->protocol->write(swoole_pack([
                    'method'=> $method,
                    'data'  => $response
                ]));
            } catch (\Exception $e) {
                Log::ERROR('Exception', Formater::exception($e));
                $this->protocol->write(swoole_pack([
                    'code'   => Error::ERR_EXCEPTION,
                ]));
                return;
            } catch (\Error $e) {
                Log::ERROR('Exception', var_export($e));
                $this->protocol->write(swoole_pack([
                    'code'  => Error::ERR_EXCEPTION,
                ]));
                return;
            }
        });

    }

    /**
     * TCP Receive
     * @param \swoole_server $server
     * @param $fd
     * @param $from_id
     * @param $data
     */
    public function onReceive(\swoole_server $server, $fd, $from_id, $data)
    {
        $data = substr($data, 4);
        $this->protocol->init($server, $fd);
        $this->handleProcess($data);
    }

    /**
     * HTTP Receive
     * @param \swoole_http_request $request
     * @param \swoole_http_response $response
     */
    public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {
        $this->protocol->init($response);
        $this->handleProcess($request->rawContent());
    }

    /**
     * WebSocket Receive
     * @param \swoole_websocket_server $server
     * @param \swoole_websocket_frame $frame
     */
    public function onMessage(\swoole_websocket_server $server, \swoole_websocket_frame $frame)
    {
        $this->protocol->init($server, $frame->fd);
        $this->handleProcess($frame->data);
    }

}
