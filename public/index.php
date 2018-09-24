<?php

define('APPLICATION_PATH', dirname(__FILE__)."/../");
$config = require APPLICATION_PATH."conf/dbConfig.php";
error_reporting(0);
Yaf_Registry::set("dbconfig",$config);
$application = new Yaf_Application( APPLICATION_PATH . "/conf/application.ini");
try{
    $application->bootstrap()->run();

    //echo 999000;

}catch(Exception $e){
    error_log($e);
}
?>
