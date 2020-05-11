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
    $id = 0;
    $pageCount = 10;
    while (true) {

        $where = array(
            "AND" => array(
                "id[>]" => $id
            ),
            "LIMIT" => array(0, $pageCount),
            "ORDER" => array(
                "id" => "ASC"
            )
        );
        $list = $articleModel->fetchAll($where);
        $data = array();
        foreach ($list as $value) {
            $data[] = array(
                "article_id" => $value['id'],
                "name" => $value['name'],
                "content" => $value['content'],
                "author" => $value['author'],
                "author_id" => $value['author_id']
            );
            $id = $value['id'];
        }
        $result = $articleEs->insertBatchData($data);
        if(count($list) < $pageCount){
            break;
        }
    }

}catch (Exception $e){
    \YC\LoggerHelper::ERR('write_baidu_url', $e->__toString());
}


