<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/3/2
 * Time: 13:50
 */

require_once "../vendor/autoload.php";
require_once "../../vendor/autoload.php";
use app\processor\SwooleService_test1_args;
use app\processor\TestRequest;


$req = new TestRequest([
        'id' => 1,
        'name' => "test",
        'lists' => [1,2,3]]
);

$args = new SwooleService_test1_args();
$args->request = $req;

$vars =
$message = [
    'method' => 'test1',
    'data' => get_object_vars($args)
];

$result = swoole_pack($message);

$client = new swoole_http_client("127.0.0.1", 9504);
$client->post('/' , $result, function(swoole_http_client $cli){
    var_dump(swoole_unpack($cli->body));
});

