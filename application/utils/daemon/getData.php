<?php
/**
 * Created by PhpStorm.
 * User: zhangyapin
 * Date: 2018/7/30
 * Time: 下午5:37
 */
require __DIR__."/../autoload.php";


try{
    $novelTmp = new NovelTmpModel();
    $where = array(
        "AND" => array(
            "status" => 1
        ),
        "LIMIT" => array(0,50)
    );
    $list = $novelTmp->fetchAll($where);
    foreach ($list as $value){
        if(empty($value['title']))continue;
        $url = $value["url"];
        $data = file_get_contents($url);
        $data = iconv('gb2312','UTF-8//IGNORE',$data);
        $contentRule = json_decode($value["content_url"],true);
        $rule = $contentRule['content'];
        preg_match("#$rule#is", $data, $contentRet);
        $content = $contentRet[$contentRule['num']];
        $novelChapter = new NovelChapterModel();
        $sqlData = array(
            "novel_id" => $value['novel_id'],
            "title" => $value['title'],
            "content" => $content,
            "keywords" => $value['title'],
            "chapter_order" => $value['order'],
            "create_time" => time(),
            "update_time" => time()
        );
        $result = $novelChapter->insert($sqlData);
        if($result){
            $novelTmp->update(array("status"=>NovelTmpModel::NOVEL_TMP_STATUS_FINISH),array("id"=>$value['id']));
        }

    }
}catch (Exception $e){
    \YC\LoggerHelper::ERR('CRON_NOVELCONTENT_getDATA', $e->__toString());
}


