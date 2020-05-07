<?php
/**
 * Created by PhpStorm.
 * User: zhangyapin
 * Date: 2018/7/30
 * Time: 下午5:37
 */
require __DIR__."/../autoload.php";


try{

    $articleEs = new \Es\ArticleModel();
    $articleModel = new ArticlesModel();
    $where = array(
        "LIMIT" => array(0,10),
        "ORDER" => array(
            "id" => "ASC"
        )
    );
    $list = $articleModel->fetchAll($where);
    echo json_encode($list);


}catch (Exception $e){
    \YC\LoggerHelper::ERR('write_baidu_url', $e->__toString());
}


