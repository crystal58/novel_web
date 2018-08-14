<?php
namespace YC\Db;
use YC\Db\Db;
class ManageDb{
    private static $_writeServer;
    private static $_readServer;
    private static $_instance;
    public static function getConfig($server){
        self::$_writeServer = (isset($server['write']) && !empty($server['write'])) ? $server['write']: array();
        self::$_readServer = (isset($server['read']) && !empty($server['read'])) ? $server['read'] : self::$_writeServer;
    }
    
    public static function getInstance($database,$isWrite = false,$farm = null){
        if($isWrite){
            $config = self::$_writeServer[$database];
            $config['writer'] = 1;
        }else{       
            $config = self::$_readServer[$database];
            $config['writer'] = 0;
        }
        if(!isset($config['database_type']) || empty($config['database_type'])){
            $config['database_type'] = "mysql";
        }
        if(!isset($config['port']) || empty($config['port'])){
            $config['port'] = 3306;
        }
        $key = "{$isWrite}:{$config['database_type']}:{$config['database_name']}:{$config['port']}:{$config['database_name']}:{$config['username']}";
        if(!isset(self::$_instance[$key]) || self::$_instance[$key] == null){
            self::$_instance[$key] = new Db($config);
        }
        return self::$_instance[$key];
        
     }

     public static function getRead($database,$farm = null){
         return self::getInstance($database,false,$farm);
     }

     public static function getWrite($database,$farm = null){
         return self::getInstance($database,true,$farm);
     }
}
