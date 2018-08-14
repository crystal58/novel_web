<?php

define("APPLICATION_PATH", dirname(__DIR__)."/../");
set_time_limit(0);
$config = require APPLICATION_PATH."/conf/dbConfig.php";
Yaf_Registry::set("dbconfig",$config);
$application = new Yaf_Application( APPLICATION_PATH . "/conf/application.ini");
$application->bootstrap();
?>
