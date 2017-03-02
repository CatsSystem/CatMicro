<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/2/21
 * Time: 15:20
 */

require_once "../vendor/autoload.php";
require_once "../../vendor/autoload.php";

use app\processor\ThriftServiceClient;
use app\processor\TestRequest;

$user_ids = range(10000, 20000);

$socket = new \Thrift\Transport\TSocket("127.0.0.1", 9503);
$transport = new \Thrift\Transport\TFramedTransport($socket);
$protocol = new \Thrift\Protocol\TBinaryProtocol($transport);
$transport->open();
$client = new ThriftServiceClient($protocol);

$req = new TestRequest([
    'id' => 1,
    'name' => "test",
    'lists' => [1,2,3]]
);
$ret = $client->test3($req, 1);
var_dump($ret);

$transport->close();