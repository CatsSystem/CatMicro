<?php

namespace app\service;

use app\processor\HproseServiceIf;
use app\processor\TestRequest;
use app\processor\TestResponse;
use base\concurrent\Promise;
use base\framework\client\Http;
use base\framework\log\Log;
use base\framework\pool\PoolManager;
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
        $mysql_pool = PoolManager::getInstance()->get('mysql_master');
        try{
            Log::DEBUG("Test", $request);
            $http = new Http("www.baidu.com");
            yield $http->init();

            $http_result = $http->get('/');
            $sql_result = MySQLStatement::prepare()
                ->select("Test",  "*")
                ->limit(0,5)
                ->query($mysql_pool->pop());

            $result = yield Promise::all([
                'http'  => $http_result,
                'sql'   => $sql_result
            ]);

            Log::DEBUG("Test", $result);
            $response->status = 200;
        } catch (\Error $e) {
            Log::DEBUG("Test", $e);
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