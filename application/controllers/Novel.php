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
            $novelId = $this->get("id");
            if($novelId <= 0){
                throw new Exception("出错了");
            }
            $novelModel = new NovelModel();
            $novelInfo = $novelModel->find($novelId);
            $this->_view->novel_info = $novelInfo;

            $novelModel = new NovelChapterModel();
            $result = $novelModel->chaptersList(array('novel_id'=>$novelId), $offset, self::PAGESIZE, true);
            $this->_view->list = $result['list'];

            $ph = new \YC\Page($result['cnt'], $page, self::PAGESIZE,"/xiaoshuo/list_{$novelId}_{num}.html");
            $this->_view->pageHtml = $ph->getPageHtml();

//            $authorModel = new AuthorModel();
//            $authorList = $authorModel->getAllAuthor();
//            $this->_view->author_list = $authorList;

        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

    public function detailAction(){

        try{
            $page = $this->get("page");
            $page = $page > 0 ? $page : 1;
            $offset = ($page-1)*50;
            $novelId = $this->get("id");
            $novelModel = new NovelModel();
            $novelInfo = $novelModel->find($novelId);

            $novelChapters = new NovelChapterModel();
            $chaptersList = $novelChapters->chaptersList(array(),$offset,50,true);
            echo json_encode($chaptersList);exit;
            $page = new \YC\Page($chaptersList['count'],$page,50);
            $pageHtml = $page->getPageHtml();

            echo json_encode($novelInfo);exit;

        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

}
