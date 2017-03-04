<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/3/4
 * Time: 15:32
 */

namespace app\cache;

use app\common\Constants;
use base\concurrent\Promise;
use base\framework\cache\ILoader;

class TestCache extends ILoader
{
    /**
     * 初始化加载器, 定义加载器id 和 tick 数量
     */
    public function init()
    {
        $this->id   = Constants::CACHE_TEST;
        $this->tick = 1;        // 1个tick的时间后刷新
    }

    /**
     * 加载缓存内容
     * @param Promise $promise
     */
    public function load(Promise $promise)
    {
        
        // 加载结果用Promise对象返回
        $promise->resolve([
            'data' => [1,2,3]
        ]);
    }


}