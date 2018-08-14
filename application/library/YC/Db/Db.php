<?php


namespace YC\Db;

require __DIR__ . '/../../medoo_mysql.php';

class Db {
    private static $_instanceList;
    private $_instance;

    private $_isWriter = false;
    private $_format = '';

    /**
    *
    * @param array $config
    * 
    *   array(
    *       'database_type' => 'mysql',
    *       'server' => '10.211.55.4',
    *       'username' => 'account',
    *       'password' => '',
    *       'database_name' => '',
    *
    *       'port' => 3306,
    *       'charset' => 'utf8',
    *       'option' => array(
    *            PDO::ATTR_PERSISTENT => false,
    *            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    *       ),
    *   )
    *
    **/
    public function __construct(array $options) {
        if(empty($options) || empty($options['database_type'])
            || empty($options['server']) || empty($options['port']) || empty($options['username']) 
            || !isset($options['password']) || empty($options['database_name'])) {
            throw new Exception("server or username or password or database_name or database_type or portcann't be empty");    
            
        }
        $opts = array(
            \PDO::ATTR_PERSISTENT => false,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_STRINGIFY_FETCHES => false,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_ORACLE_NULLS => \PDO::NULL_TO_STRING,
        );

        $options['charset'] = isset($options['charset']) ? $options['charset'] : 'utf8';
        $options['option'] = isset($option['option']) ? array_merge($opts, $option['option']) : $opts;

        $key = "{$options['database_type']}:{$options['server']}:{$options['port']}:{$options['database_name']}:{$options['username']}";
        
        if(!isset(self::$_instanceList[$key])) {
            self::$_instanceList[$key] = new \medoo_mysql($options);
        }
        $this->_instance = self::$_instanceList[$key];

        if(isset($options) && isset($options['writer']) && $options['writer']) {
            $this->_isWriter = true; 
        }
        $this->_format = isset($options['db_format']) ? $options['db_format'] : '';
    }

    public function getFormat() {
        return $this->_format;
    }

    public function query($query) {
        // 主库，或者非写SQL
        if($this->_isWriter || !$this->_isWriteSql($query)) {
            return $this->_instance->query($query);
        }
        throw new ReadOnlyException("Cann't write data to reader");
    }

    public function exec($query) {
        // 主库，或者非写SQL
        if($this->_isWriter || !$this->_isWriteSql($query)) {
            return $this->_instance->exec($query);
        }
        throw new ReadOnlyException("Cann't write data to reader");
    }

    public function __call($method, $args) {
        $method = strtolower($method);
        if($this->_isWriter || !in_array($method, array("insert", "update", "delete", "replace", "begin", "commit", "rollback"))) {
            return call_user_func_array(array($this->_instance, $method), $args);
        }
        throw new ReadOnlyException("Cann't write data to reader!!");
    }

    protected function _isWriteSql($sql) {
        $sql = ltrim($sql);
        $str = strtoupper(substr($sql, 0, strpos($sql, " ")));
        return $str != "SELECT" && $str != "DESC" && $str != "EXPLAIN";
    }

}

