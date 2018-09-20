<?php


class NovelController extends AbstractController{
    const PAGESIZE = 50;

    /**
     * 小说列表
     */
    public function listAction(){

        try {
            $page = $this->get("page", 1);
            $page = $page > 0 ? $page : 1;
            $offset = ($page - 1) * self::PAGESIZE;
            $authorId = $this->get("author_id");
            if($authorId <= 0 ){
                throw new Exception("出错了");
            }
            $authorModel = new AuthorModel();
            $authorInfo = $authorModel->find($authorId);
            $this->_view->author_info =$authorInfo;

            $novelModel = new NovelModel();
            $novelList = $novelModel->novelList(array("author_id" => $authorId));
            $this->_view->novel_list = $novelList['list'];
            //echo json_encode($novelList);exit;
            //$ph = new \YC\Page($result['cnt'], $page, self::PAGESIZE,"/xiaoshuo/list_{$novelId}_{num}.html");
            //$this->_view->pageHtml = $ph->getPageHtml();


        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

    public function detailAction(){

        try{
            $chapterId = $this->get("id");
            $novelChapterModel = new NovelChapterModel();
            $params = array("id" => $chapterId);
            $novelChapter = $novelChapterModel->chapter($params);

            if(empty($novelChapter)){
                throw new Exception("章节不存在!",404);
            }

            $novelId = $novelChapter['novel_id'];
            $novelModel = new NovelModel();
            $novelInfo = $novelModel->find($novelId);

            $novelChapter['next'] = $novelChapter['pre'] = false;
            $params = array(
                "AND" => array(
                    "novel_id" => $novelId,
                    "id[>]" =>$novelChapter['id']
                ),
                "ORDER" => array(
                    "chapter_order" => "ASC",
                    "id" => "ASC"
                )
            );
            $nextNovel = $novelChapterModel->fetchRow($params,array("id"));
            if($nextNovel){
                $novelChapter['next'] = $nextNovel['id'];
            }
            $params = array(
                "AND" => array(
                    "novel_id" => $novelId,
                    "id[<]" =>$novelChapter['id']
                ),
                "ORDER" => array(
                    "chapter_order" => "DESC",
                    "id" => "DESC"
                )
            );
            $preNovel = $novelChapterModel->fetchRow($params,array("id"));
            if($preNovel){
                $novelChapter['pre'] = $preNovel['id'];
            }

            $authorNovel = $novelModel->novelList(array("author_id"=>$novelInfo['author_id']),0,6);
            $relateNovel = $novelModel->novelList(array("novel_class_id"=>$novelInfo['novel_class_id']),0,6);

//echo json_encode($relateNovel);exit;
            $this->_view->chapter = $novelChapter;
            $this->_view->novel = $novelInfo;
            $this->_view->author_novel = $authorNovel['list'];
            $this->_view->relate_novel = $relateNovel['list'];


        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

    public function chapterAction(){
        try{
            $page = $this->get("page");
            $page = $page > 0 ? $page : 1;
            $offset = ($page-1)*50;
            $novelId = $this->get("id");

            $novelChapters = new NovelChapterModel();
            $params = array("novel_id" => $novelId);
            $chaptersList = $novelChapters->chaptersList($params,$offset,100,true);
            $this->_view->list = $chaptersList['list'];

            $novelModel = new NovelModel();
            $novelInfo = $novelModel->find($novelId);
            $this->_view->novel_info = $novelInfo;
            //echo json_encode($chaptersList);exit;

        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }

    }

}
