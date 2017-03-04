<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/3/4
 * Time: 15:41
 */

namespace app\task;

use base\common\Globals;
use base\framework\pool\PoolManager;
use base\framework\task\IRunner;
use base\model\MySQLStatement;

class TestTask extends IRunner
{
    public function test_task($id, $name, $arr)
    {
        Globals::var_dump($id);
        Globals::var_dump($name);
        Globals::var_dump($arr);

        $mysql_pool = PoolManager::getInstance()->get('mysql_master');
        $sql_result = MySQLStatement::prepare()
            ->select("Test",  "*")
            ->limit(0,2)
            ->query($mysql_pool->pop());

        return $sql_result;
    }
}