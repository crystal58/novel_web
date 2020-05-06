<?php


class NovelController extends AbstractController{
    const PAGESIZE = 48;
    const AUTHOR_NOVEL_PAGESIZE = 50;

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->_active = "novel";
        $this->_view->active = $this->_active;
    }


    /**
     * 作者的小说列表
     */
    public function authorlistAction(){

        try {
            $page = $this->get("page", 1);
            $page = $page > 0 ? $page : 1;
            $offset = ($page - 1) * self::AUTHOR_NOVEL_PAGESIZE;
            $authorId = $this->get("author_id");
            if($authorId <= 0){
                throw new Exception("出错了");
            }
            $authorModel = new AuthorModel();
            $authorInfo = $authorModel->find($authorId);
            $this->_view->author_info =$authorInfo;

            $novelModel = new NovelModel();
            $params = array(
                "author_id" => $authorId,
                "status"=>NovelModel::NOVEL_STATUS_OK,
                "record_status" => NovelModel::NOVEL_RECORDING_FINISH
            );
            $novelList = $novelModel->novelList($params,$offset,self::AUTHOR_NOVEL_PAGESIZE,true,array("order"=>"ASC"));
            $this->_view->novel_list = $novelList['list'];

            $this->_view->seo = array(
                "title" => isset($authorInfo['author_name'])?str_replace(array("{author}"),array($authorInfo['author_name']),$this->_seo['author']['title']):"",
                "keywords" => isset($authorInfo['author_name'])?str_replace(array("{author}"),array($authorInfo['author_name']),$this->_seo['author']['keywords']):"",
                "description" => str_replace(array("{author}"),array($authorInfo['author_name']),$this->_seo['author']['description']).mb_substr(strip_tags($authorInfo['description']),0,80,'utf-8')
            );
            $this->_view->page_num = ceil($novelList['cnt']/self::AUTHOR_NOVEL_PAGESIZE);
            $this->_view->page_url = $this->_webUrl."/xiaoshuo/author_".$authorId."_{page}.html";
            $this->_view->cur_page = $page;
           // var_dump($this->_view);exit;
            //echo json_encode($novelList);exit;
            //$ph = new \YC\Page($result['cnt'], $page, self::PAGESIZE,"/xiaoshuo/list_{$novelId}_{num}.html");
            //$this->_view->pageHtml = $ph->getPageHtml();


        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

    /**
     * 分类小说列表 暂时屏蔽
     */
    public function listAction(){
        try {
            $page = $this->get("page", 1);
            $page = $page > 0 ? $page : 1;
            $offset = ($page - 1) * self::PAGESIZE;
            $classTypeId = $this->get("id");
            if ($classTypeId <= 0) {
                throw new Exception("出错了");
            }
            $novelModel = new NovelModel();
            $novelData = $novelModel->novelList(array("novel_class_id"=>$classTypeId));
            $this->_view->novel_list = $novelData['list'];

            $this->_view->class_type_id = $classTypeId;
        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

    public function detailAction(){

        try{
            $chapterId = $this->get("id");
            $novelChapterModel = new NovelChapterModel();
            $params = array("id" => $chapterId,"status" => 1);
            $novelChapter = $novelChapterModel->chapter($params);

            $novelChapter['content'] = $this->filterWord($novelChapter['content']);

            $novelId = $novelChapter['novel_id'];
            $novelModel = new NovelModel();

            $novelInfo = $novelModel->find($novelId);
            if(empty($novelInfo) || $novelInfo['status']!=1){
                header("Location:https://www.eeeaaa.cn");
                exit;
                throw new Exception("章节不存在!",404);

            }
            $novelChapter['next'] = $novelChapter['pre'] = false;
            $params = array(
                "AND" => array(
                    "novel_id" => $novelId,
                    "chapter_order[>]" =>$novelChapter['chapter_order']
                ),
                "ORDER" => array(
                    "chapter_order" => "ASC",
                    "id" => "ASC"
                )
            );
            $nextNovel = $novelChapterModel->fetchRow($params,array("id","chapter_order"));
            if($nextNovel){
                $novelChapter['next'] = $nextNovel['id'];
            }
            $params = array(
                "AND" => array(
                    "novel_id" => $novelId,
                    "chapter_order[<]" =>$novelChapter['chapter_order']
                ),
                "ORDER" => array(
                    "chapter_order" => "DESC",
                    "id" => "ASC"
                )
            );
            $preNovel = $novelChapterModel->fetchRow($params,array("id","chapter_order"));
            if($preNovel){
                $novelChapter['pre'] = $preNovel['id'];
            }

            $authorNovel = $novelModel->novelList(array("author_id"=>$novelInfo['author_id'],"record_status[!]" => NovelModel::NOVEL_RECORDING_INIT),0,6);
            $relateNovel = $novelModel->novelList(array("novel_class_id"=>$novelInfo['novel_class_id'],"record_status[!]" => NovelModel::NOVEL_RECORDING_INIT),0,6);

//echo json_encode($relateNovel);exit;
            $this->_view->chapter = $novelChapter;
            $this->_view->novel = $novelInfo;
            $this->_view->author_novel = $authorNovel['list'];
            $this->_view->relate_novel = $relateNovel['list'];
            $authorModel = new AuthorModel();
            $authorInfo = $authorModel->find($novelInfo['author_id']);
            $this->_view->seo = array(
                "title" => (!empty($novelInfo))?str_replace(array("{author}","{book}","{chaptertitle}"),array($authorInfo['author_name'],$novelInfo['name'],$novelChapter['title']),$this->_seo['noveldetail']['title']):"",
                "keywords" => !empty($novelInfo)?str_replace(array("{author}","{book}","{chaptertitle}"),array($authorInfo['author_name'],$novelInfo['name'],$novelChapter['title']),$this->_seo['noveldetail']['keywords']):"",
                "description" => !empty($novelInfo)?str_replace(array("{author}","{book}","{chaptertitle}"),array($authorInfo['author_name'],$novelInfo['name'],$novelChapter['title']),$this->_seo['noveldetail']['description']):"",
            );



        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

    public function chapterAction(){
        try{
            $page = $this->get("page");
            $page = $page > 0 ? $page : 1;
            $offset = ($page-1)*self::PAGESIZE;
            $novelId = $this->get("id");

            $novelChapters = new NovelChapterModel();
            $params = array(
                "novel_id" => $novelId,
                "status" => 1
            );
            $chaptersList = $novelChapters->chaptersList($params,$offset,self::PAGESIZE,true);
            //$chaptersList = $novelChapters->chaptersList($params);
            $this->_view->list = $chaptersList['list'];

            $novelModel = new NovelModel();
            $novelInfo = $novelModel->find($novelId);

            if(empty($novelInfo) || $novelInfo['status']!=1){
                header("Location:https://www.eeeaaa.cn");
                exit;
            }
            $this->_view->novel = $novelInfo;

            $authorModel = new AuthorModel();
            $authorInfo = $authorModel->find($novelInfo['author_id']);

            $this->_view->page_num = ceil($chaptersList['cnt']/self::PAGESIZE);
            $this->_view->page_url = $this->_webUrl."/gudian/chapter_".$novelId."_{page}.html";
            $this->_view->cur_page = $page;

            $this->_view->seo = array(
                "title" => isset($novelInfo['name'])?str_replace(array("{author}","{book}"),array($authorInfo['author_name'],$novelInfo['name']),$this->_seo['novelchapter']['title']):"",
                "keywords" => isset($novelInfo['name'])?str_replace(array("{author}","{book}"),array($authorInfo['author_name'],$novelInfo['name']),$this->_seo['novelchapter']['keywords']):"",
                "description" => (isset($novelInfo['name'])?str_replace(array("{author}","{book}"),array($authorInfo['author_name'],$novelInfo['name']),$this->_seo['novelchapter']['description']):"").mb_substr(strip_tags($novelInfo['content']),0,80,'utf-8')
            );
            //echo json_encode($chaptersList);exit;

        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }

    }

    /**
     * 古典小说
     */
    public function classicalAction(){
        try{

            $page = $this->get("page");
            $page = $page > 0 ? $page : 1;
            $offset = ($page-1)*self::PAGESIZE;

            $novelModel = new NovelModel();
            $params = array(
                "novel_class_id" => 7,
                "status" => 1
            );
            $novelList = $novelModel->novelList($params,$offset,self::PAGESIZE,true,array("id" => "ASC"));
            $this->_view->novel = $novelList['list'];

            $this->_view->page_num = ceil($novelList['cnt']/self::PAGESIZE);
            $this->_view->page_url = $this->_webUrl."/gudian/gudian_7_{page}.html";
            $this->_view->cur_page = $page;

            $this->_view->gudian = array(

            );

            $this->_view->seo = array(
                "title" => "古典小说在线阅读_文学星空",
                "keywords" => "古典小说,古典小说在线阅读",
                "description" => "文学星空古典小说频道，提供经典好看的古典小说免费在线阅读和下载。"
            );
            //echo json_encode($chaptersList);exit;

        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

}
