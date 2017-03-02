<?php
/**
 * User: shenzhe
 * Date: 13-6-17
 */


namespace base\protocol;

abstract class Protocol
{
    abstract public function write($data);
}
