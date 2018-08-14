<?php

define('APPLICATION_PATH', dirname(__FILE__)."/../");
$config = require APPLICATION_PATH."conf/dbConfig.php";

Yaf_Registry::set("dbconfig",$config);
$application = new Yaf_Application( APPLICATION_PATH . "/conf/application.ini");
try{
    $application->bootstrap()->run();
}catch(Exception $e){
    error_log($e);
}
?>
