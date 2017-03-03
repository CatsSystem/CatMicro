<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/3/3
 * Time: 12:57
 */

namespace base\framework\pool;

abstract class BasePool
{
    /**
     * @var \SplQueue 空闲队列
     */
    protected $idle_queue;

    /**
     * @var int 池大小
     */
    protected $size;

    /**
     * @var string 名称
     */
    protected $name;

    public function __construct($name, $size)
    {
        $this->name         = $name;
        $this->size         = $size;
        $this->idle_queue   = new \SplQueue();
    }

    /**
     * 弹出一个空闲item
     * @return mixed
     */
    abstract public function pop();

    /**
     * 归还一个item
     * @param mixed $item
     * @param bool $close   是否关闭
     * @return mixed
     */
    abstract public function push(mixed $item, $close = false);

    /**
     * 添加一个任务到等待队列中
     * @param WaitTask $task
     * @return mixed
     */
    abstract public function wait(WaitTask $task);

    /**
     * 初始化连接池
     */
    abstract public function init();

    /**
     * @param $id
     * @return mixed
     */
    abstract protected function new_item($id);
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}