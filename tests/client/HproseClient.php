<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/3/1
 * Time: 15:19
 */

require_once '../vendor/autoload.php';
require_once '../../vendor/autoload.php';

$client = new \Hprose\Socket\Client('tcp://127.0.0.1:9502', false);

$ids = [6238180604807987001, 6238180604807987122];
$message = new \app\processor\addVideoRequest(array('userId' => strval(10000), 'bucket' => 'KgUniqueFeed','videoIds' => $ids));

$response = $client->addVideo($message);

var_dump($response);
