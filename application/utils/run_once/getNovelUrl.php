<?php
/**
 * Created by PhpStorm.
 * User: zhangyapin
 * Date: 2018/7/30
 * Time: 下午5:37
 */
require __DIR__."/../autoload.php";


try{
    $authorModel = new AuthorModel();
    $novelModel = new NovelModel();
    $novelChapter = new NovelChapterModel();
    $offset = 0;
    $pagesize = 500;
    $file = APPLICATION_PATH."public/novel";
    $authorNovelPageSize = NovelController::AUTHOR_NOVEL_PAGESIZE;
    $sumCount = 0;
    $fileName = "";
    while (true){
        $where = array("status"=>AuthorModel::AUTHOR_STATUS);
        $authorList = $authorModel->getList($where,$offset,$pagesize);
        foreach ($authorList['list'] as $value){
            $novelWhere = array(
                "AND" => array(
                    "author_id" => $value['id'],
                    "status"=>NovelModel::NOVEL_STATUS_OK,
                    "record_status" => NovelModel::NOVEL_RECORDING_FINISH
                )
            );
            $novelList = $novelModel->fetchAll($novelWhere);
            $novelCount = count($novelList);
            $count = ceil($novelCount/$authorNovelPageSize);
            $url = "";
            for($i = 1 ; $i<=$count;$i++){
                $url .= "https://www.eeeaaa.cn/xiaoshuo/author_".$value['id']."_".$i.".html\r\n";
                $sumCount++;
            }

            foreach ($novelList as $novelValue){


                $chapterWhere = array(
                    "AND" => array(
                        "novel_id" => $novelValue['id'],
                        "status" => 1,
                    )
                );
                $novelChapterList = $novelChapter->fetchAll($chapterWhere);
                $novelChapterCount = count($novelChapterList);
                $pageCount = ceil($novelChapterCount/NovelController::PAGESIZE);
                for($i = 1 ; $i<=$pageCount;$i++){
                    $url .= "https://www.eeeaaa.cn/xiaoshuo/chapter_".$novelValue['id']."_".$i.".html\r\n";
                    $sumCount++;
                }
                foreach ($novelChapterList as $chapterValue){
                    if($chapterValue['is_part'] == 1)continue;
                    $url .= "https://www.eeeaaa.cn/xiaoshuo/detail_".$chapterValue['id'].".html\r\n";
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
        //echo $fileName;
        if(count($authorList['list']) < $pagesize){
            break;
        }

    }

}catch (Exception $e){
    \YC\LoggerHelper::ERR('write_baidu_url', $e->__toString());
}


