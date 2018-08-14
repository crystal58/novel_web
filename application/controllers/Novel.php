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
    public function addAction(){

        try {
            if ($this->getRequest()->isPost()) {
                $name = $this->getPost("name");
                if (empty($name)) {
                    throw new Exception("参数错误(name={$name})", 400);
                }
                $author = $this->getPost("author");
                $authorData = explode("_",$author);
                if(count($authorData)!= 2){
                    throw new Exception("参数错误（author={$author}）",400);
                }
                $description = $this->getPost("description");
                $params = array(
                    "name" => $name,
                    "author_id" => $authorData[0],
                    "author_name" => $authorData[1],
                    "content" => $description,
                    "operator_id" => $this->_operatorId,
                    "create_time" => time(),
                    "update_time" => time(),
                    "novel_class_id" => $this->getPost("novel_class"),
                    "novel_id" => $this->getPost("novel_id"),
                    "record_status" => $this->getPost("record_status")

                );
                //echo json_encode($params);exit;
                $novelModel = new NovelModel();
                $return = $novelModel->replaceNovel($params);
                if(!$return){
                    throw new Exception("操作失败");
                }
                $this->redirect("/novel/list");
            }
        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }
    public function editStatusAction(){
        $type = $this->get("type");
        $id = $this->get("id");
        try{
            if(!in_array($type,array("del","limit"))){
                throw new Exception("参数错误（type={$type}）",400);
            }
            if($type == "del"){
                $status = NovelModel::NOVEL_STATUS_DEL;
            }else if($type == "limit"){
                $status = NovelModel::NOVEL_STATUS_LIMIT;
            }else{
                $status = NovelModel::NOVEL_STATUS_OK;
            }
            $novelModel = new NovelModel();
            $ret = $novelModel->update(array("status"=>$status),array("id"=>(int)$id));
            //var_dump($ret);exit;
            $this->redirect("/novel/list");
        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

    /**
     * 目录管理
     */
    public function subjectAction(){
        try{
            $id = $this->get("id");
            if($id <= 0){
                throw new Exception("参数错误（id={$id}）",400);
            }
            $page = (int)$this->get("page",1);
            $page = $page>0 ? $page :1;
            $offset = ($page-1) * self::PAGESIZE;

            $novelModel = new NovelTmpModel();
            $result = $novelModel->getList(array("novel_id" => (int)$id),$offset,self::PAGESIZE,true);
            $this->_view->list = $result['list'];
            $ph = new \YC\Page($result['cnt'],$page,self::PAGESIZE);
           // echo json_encode(array($result['cnt'],$page,self::PAGESIZE));
            $this->_view->pageHtml = $ph->getPageHtml();
        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

    public function subjectStatusAction(){
        try{
            $novelId = $this->get("id");
            if($novelId > 0){
                $novelModel = new NovelTmpModel();
                $result = $novelModel->update(array("status" => NovelTmpModel::NOVEL_TMP_STATUS_READY),array("novel_id" => $novelId));
            }
            $this->redirect("/novel/subject?id=".$novelId);
        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

}
