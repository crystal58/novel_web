<?php
/**
 * Created by PhpStorm.
 * User: zhangyapin
 * Date: 2018/7/30
 * Time: 下午5:37
 */
require __DIR__."/../autoload.php";


try{
    $articleType = new ArticlesTypeModel();
    $offset = 0;
    $pagesize = 500;
    $file = APPLICATION_PATH."public/article";
    $sumCount = 0;
    $fileName = "";
    $articleType = array("1" => "tangshi","2"=>"ciqu","4"=>"ciqu");
    $pathType = array("1"=>"gushi","2" => "songci","4"=>"yuanqu");
    $articleTypeModel = new ArticlesTypeModel();
    $articleAuthorModel = new ArticleAuthorModel();
    $articleModel = new ArticlesModel();
    foreach ($articleType as $key=>$value){
        $url = "https://www.eeeaaa.cn/".$value."/list_1.html\r\n";
        $sumCount++;
        $params = array(
            "status" => ArticlesTypeModel::ARTICLE_CLASS_STATUS,
            "parent_id" => $key
        );
        $articleType = $articleTypeModel->getList($params);

        $authorParams = array(
            "status" => ArticleAuthorModel::AUTHOR_STATUS,
            "class_type_id" => $key
        );
        $articleAuthor = $articleAuthorModel->getList($authorParams);

        $page = ceil((count($articleType['list']) + count($articleAuthor['list']))/ArticleController::PAGESIZE);
        for($i = 2; $i<=$page;$i++){
            $url .= "https://www.eeeaaa.cn/".$value."/list_1_".$i.".html\r\n";
            $sumCount++;
        }

        foreach ($articleType['list'] as $typeValue){
            $articleParams = array(
                "class_type" => (int)$typeValue['id'],
                "status" => 1
            );
            $chaptersList = $articleModel->getList($articleParams);
            $articleCount = count($chaptersList['list']);
            $articlePage = ceil($articleCount/ArticleController::PAGESIZE);
            for($j=1;$j<=$articlePage;$j++){
                $url .= "https://www.eeeaaa.cn/".$value."/chapter_".$typeValue['id']."_".$j.".html\r\n";
                $sumCount++;
            }
            foreach($chaptersList['list'] as $chapterValue){
                if($chapterValue['is_part'] == 1)continue;
                $url .= "https://www.eeeaaa.cn/".$value."/detail_".$chapterValue['id'].".html\r\n";
                $sumCount++;
            }
        }

        foreach ($articleAuthor['list'] as $authorValue){
            $articleParams = array(
                "author_id" => (int)$authorValue['id'],
                "status" => 1
            );
            $chaptersList = $articleModel->getList($articleParams);
            $articleCount = count($chaptersList['list']);
            $articlePage = ceil($articleCount/ArticleController::PAGESIZE);
            for($j=1;$j<=$articlePage;$j++){
                $url .= "https://www.eeeaaa.cn/".$value."/".$pathType[$key]."_".$authorValue['id']."_".$j.".html\r\n";
                $sumCount++;
            }
            foreach($chaptersList['list'] as $chapterValue){
                if($chapterValue['is_part'] == 1)continue;
                $url .= "https://www.eeeaaa.cn/".$value."/detail_".$chapterValue['id'].".html\r\n";
                $sumCount++;
            }
        }
        $fileNameNew = $file.ceil($sumCount/50000).".txt";
        if($fileName != $fileNameNew){
            $fileName = $fileNameNew;
            file_put_contents($fileName,"");
        }

        file_put_contents($fileName,$url,FILE_APPEND);

    }

}catch (Exception $e){
    \YC\LoggerHelper::ERR('write_baidu_url', $e->__toString());
}


