<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/3/2
 * Time: 13:50
 */

require_once "../vendor/autoload.php";
require_once "../../vendor/autoload.php";
use app\processor\SwooleService_test3_args;
use app\processor\TestRequest;


$req = new TestRequest([
        'id' => 1,
        'name' => "test",
        'lists' => [1,2,3]]
);

$args = new SwooleService_test3_args();
$args->request = $req;
$args->id = 1;

$vars =
$message = [
    'method' => 'test3',
    'data' => get_object_vars($args)
];

$result = swoole_pack($message);

$client = new swoole_http_client("127.0.0.1", 9502);
$client->post('/' , $result, function(swoole_http_client $cli){
    var_dump($cli->body);
    //var_dump(swoole_unpack($cli->body));
});

