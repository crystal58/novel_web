<?php


abstract class YCL_Queue_Abstract {
    abstract public function push($key, $value, $pri = null, $delay = null, $ttr = null);
    abstract public function pop($key);
    public function begin() {}
    public function rollback() {}
    public function commit() {}
    public function consume($key, callable  $callback, $options = array()) {}

    // 为了实现多个环境中的队列互不影响，所以考虑在队列前面添加一个字符串
    protected static $_keyPrefix = '';
    public static function setKeyPrefix($prefix) {
        self::$_keyPrefix = (string) $prefix;
    }

    protected function _getKey($key) {
        return self::$_keyPrefix . $key;
    }
}
