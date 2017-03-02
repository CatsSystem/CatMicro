<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/3/1
 * Time: 15:19
 */

require_once '../vendor/autoload.php';
require_once '../../vendor/autoload.php';

use app\processor\TestRequest;

$client = new \Hprose\Socket\Client('tcp://127.0.0.1:9502', false);
var_dump("test");
$req = new TestRequest([
        'id' => 1,
        'name' => "test",
        'lists' => [1,2,3]
    ]
);
var_dump($req);
$response = $client->test3($req, 1);

var_dump($response);
