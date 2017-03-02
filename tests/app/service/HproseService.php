<?php

namespace app\service;

use app\processor\HproseServiceIf;
use app\processor\TestRequest;
use app\processor\TestResponse;
use base\async\http\AsyncHttpClient;
use base\model\MySQLStatement;

/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/2/21
 * Time: 15:02
 */

class HproseService implements HproseServiceIf
{
    /**
     * @param TestRequest $request
     * @return \app\processor\TestResponse
     */
    public function test1(TestRequest $request)
    {
        $response = new TestResponse();
        try{
            var_dump($request);
            $http = new AsyncHttpClient("www.baidu.com");
            $result = yield $http->init();
            var_dump($result);
            $result = yield $http->get('/');
            var_dump($result);

            $result = yield MySQLStatement::prepare()
                ->select("Test",  "*")
                ->query();
            var_dump($result);
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
        var_dump("test");
        return $id;
    }

    /**
     * @param TestRequest $request
     * @param int $id
     * @return int
     */
    public function test3(TestRequest $request, $id)
    {
        var_dump("Hprose Service");
        return $id;
    }
}