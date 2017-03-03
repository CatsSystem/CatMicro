<?php

namespace app\service;

use app\processor\TestRequest;
use app\processor\TestResponse;
use app\processor\ThriftServiceIf;
use base\async\http\AsyncHttpClient;
use base\framework\log\Log;
use base\model\MySQLStatement;

/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/2/21
 * Time: 15:02
 */

class ThriftService implements ThriftServiceIf
{
    /**
     * @param TestRequest $request
     * @return \app\processor\TestResponse
     */
    public function test1(TestRequest $request)
    {
        $response = new TestResponse();
        try{
            Log::DEBUG("Test", $request);
            $http = new AsyncHttpClient("www.baidu.com");
            $result = yield $http->init();
            Log::DEBUG("Test", $result);
            $result = yield $http->get('/');
            Log::DEBUG("Test", $result);

            $result = yield MySQLStatement::prepare()
                ->select("Test",  "*")
                ->limit(0,5)
                ->query();
            Log::DEBUG("Test", $result);
            $response->status = 200;
        } catch (\Error $e) {
            $response->status = 503;
        }
        return $response;
    }

    /**
     * @param string $name
     * @param int $id
     * @return int
     */
    public function test2($name, $id)
    {
        var_dump("unique");
        return $id;
    }

    /**
     * @param TestRequest $request
     * @param int $id
     * @return int
     */
    public function test3(TestRequest $request, $id)
    {
        var_dump("Thrift Service");
        return $id;
    }
}