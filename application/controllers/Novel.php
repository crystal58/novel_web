<?php


class NovelController extends AbstractController{
    const PAGESIZE = 50;

    /**
     * 小说列表
     */
    public function listAction(){

        try {
            $page = ((int)$this->get("page", 1)) > 0 ?: 1;
            $offset = ($page - 1) * self::PAGESIZE;
            $novelModel = new NovelModel();
            $result = $novelModel->novelList(array(), $offset, self::PAGESIZE, true);
            $this->_view->novel_list = $result['list'];

            $ph = new \YC\Page($result['cnt'], $page, self::PAGESIZE);
            $this->_view->pageHtml = $ph->getPageHtml();

            $authorModel = new AuthorModel();
            $authorList = $authorModel->getAllAuthor();
            $this->_view->author_list = $authorList;

           // $result['list'] = array();
            $this->_view->list = $result['list'];

        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

    public function detailAction(){

    }

}
