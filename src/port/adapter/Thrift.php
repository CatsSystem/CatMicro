<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/2/28
 * Time: 14:26
 */

namespace base\port\adapter;

use base\port\BasePort;
use base\protocol\Protocol;
use core\common\Formater;
use core\concurrent\Promise;
use core\component\log\Log;
use Thrift\Exception\TApplicationException;
use Thrift\Protocol\TBinaryProtocol;
use Thrift as TThrift;
use Thrift\Exception\TTransportException;
use Thrift\Protocol\TBinaryProtocolAccelerated;
use Thrift\Type\TMessageType;
use Thrift\Type\TType;

class Thrift extends BasePort
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

    }

    protected function handleProcess($data)
    {
        Promise::co(function() use($data) {
            $socket = new ThriftTransport();
            $socket->writer = $this->protocol;
            $socket->buffer = $data;

            $protocol = new TBinaryProtocol($socket, false, false);

            try {
                $processor_path = $this->config['processor_path'];
                $handler_class = $this->config['service_path'];
                $handler = new $handler_class();

                $rseqid = 0;
                $fname = null;
                $mtype = 0;

                $protocol->readMessageBegin($fname, $mtype, $rseqid);

                if (!method_exists($handler, $fname)) {
                    $protocol->skip(TType::STRUCT);
                    $protocol->readMessageEnd();
                    $x = new TApplicationException('Function ' . $fname . ' not implemented.', TApplicationException::UNKNOWN_METHOD);
                    $protocol->writeMessageBegin($fname, TMessageType::EXCEPTION, $rseqid);
                    $x->write($protocol);
                    $protocol->writeMessageEnd();
                    $protocol->getTransport()->flush();
                    return;
                }

                $args_name = $processor_path . "_{$fname}_args";
                $result_name = $processor_path . "_{$fname}_result";
                $args = new $args_name();
                $args->read($protocol);
                $protocol->readMessageEnd();
                $result = new $result_name();
                $result->success = yield call_user_func_array([$handler,$fname], get_object_vars($args));
                $bin_accel = ($protocol instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
                if ($bin_accel) {
                    \thrift_protocol_write_binary($protocol, $fname, TMessageType::REPLY, $result, $rseqid, $protocol->isStrictWrite());
                } else {
                    $protocol->writeMessageBegin($fname, TMessageType::REPLY, $rseqid);
                    $result->write($protocol);
                    $protocol->writeMessageEnd();
                    $protocol->getTransport()->flush();
                }

            } catch (\Exception $e) {
                Log::ERROR('Exception', Formater::exception($e));
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

class ThriftTransport extends TThrift\Transport\TFramedTransport
{
    public $buffer = '';
    public $offset = 0;
    /**
     * @var Protocol
     */
    public $writer;

    protected $read_ = true;
    protected $rBuf_ = '';
    protected $wBuf_ = '';

    function readFrame()
    {
        $buf = $this->_read(4);
        $val = unpack('N', $buf);
        $sz = $val[1];
        $this->rBuf_ = $this->_read($sz);
    }

    public function _read($len)
    {
        if (strlen($this->buffer) - $this->offset < $len)
        {
            throw new TTransportException('TSocket['.strlen($this->buffer).'] read '.$len.' bytes failed.');
        }
        $data = substr($this->buffer, $this->offset, $len);
        $this->offset += $len;
        return $data;
    }

    public function read($len) {
        if (!$this->read_) {
            return $this->_read($len);
        }
        if (TThrift\Factory\TStringFuncFactory::create()->strlen($this->rBuf_) === 0) {
            $this->readFrame();
        }
        if ($len >= TThrift\Factory\TStringFuncFactory::create()->strlen($this->rBuf_)) {
            $out = $this->rBuf_;
            $this->rBuf_ = null;
            return $out;
        }
        $out = TThrift\Factory\TStringFuncFactory::create()->substr($this->rBuf_, 0, $len);
        $this->rBuf_ = TThrift\Factory\TStringFuncFactory::create()->substr($this->rBuf_, $len);
        return $out;
    }

    public function write($buf)
    {
        $this->wBuf_ .= $buf;
    }

    public function flush()
    {
        $out = pack('N', strlen($this->wBuf_));
        $out .= $this->wBuf_;
        $this->writer->write($out);
        $this->wBuf_ = '';
    }
}