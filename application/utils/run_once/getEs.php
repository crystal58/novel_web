<?php
/**
 * Created by PhpStorm.
 * User: zhangyapin
 * Date: 2018/7/30
 * Time: 下午5:37
 */
require __DIR__."/../autoload.php";


try{

    $article = new \Es\ArticleModel();


}catch (Exception $e){
    \YC\LoggerHelper::ERR('write_baidu_url', $e->__toString());
}


