<?php


class ArticleController extends AbstractController{
    const PAGESIZE = 50;

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->_active = "tang";
        $this->_view->active = $this->_active;
    }


    /**
     *
     */
    public function listAction(){

        try {
            $page = $this->get("page", 1);
            $page = $page > 0 ? $page : 1;
            $offset = ($page - 1) * self::PAGESIZE;

            $classTypeId = $this->get("id",1);

            $params = array(
                "status" => ArticlesTypeModel::ARTICLE_CLASS_STATUS,
                "parent_id" => $classTypeId
            );
            $articleTypeModel = new ArticlesTypeModel();
            $articleType = $articleTypeModel->getList($params);
            $this->_view->article_type =$articleType['list'];

            $key = "";
            switch ($classTypeId){
                case ArticlesTypeModel::ARTICLE_TYPE_TANG :
                    $key = "suitang";
                    break;
                case ArticlesTypeModel::ARTICLE_TYPE_SONG:
                    $key = "songyuan";
                    break;

            }

            $this->_view->seo = array(
                "title" => $this->_seo[$key]['title'],
                "keywords" => $this->_seo[$key]['keywords'],
                "description" => $this->_seo[$key]['description'],
            );


            //$ph = new \YC\Page($result['cnt'], $page, self::PAGESIZE,"/xiaoshuo/list_{$novelId}_{num}.html");
            //$this->_view->pageHtml = $ph->getPageHtml();


        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

    public function detailAction(){

        try{
            $articleId = $this->get("id");
            $articleModel = new ArticlesModel();
            $articleInfo = $articleModel->find($articleId);


            if(empty($articleInfo)){
                throw new Exception("章节不存在!",404);
            }

            $this->_view->article_info = $articleInfo;
            $classId = $articleInfo['class_type'];
            $articleTypeModel = new ArticlesTypeModel();
            $articleType = $articleTypeModel->find($classId);

            $this->_view->article_type = $articleType;

            $params = array(
                "status" => ArticlesModel::ARTICLE_CLASS_STATUS,
                "id[!]" => $articleId
            );
            $relateArticle = $articleModel->getList($params,0,10);
            $this->_view->relate_article = $relateArticle['list'];

            $articleChapter['next'] = $articleChapter['pre'] = false;
            $params = array(
                "AND" => array(
                    "id[>]" =>$articleId
                ),
                "ORDER" => array(
                    "id" => "ASC"
                )
            );
            $nextArticle = $articleModel->fetchRow($params,array("id"));
            if($nextArticle){
                $articleChapter['next'] = $nextArticle['id'];
            }
            $params = array(
                "AND" => array(
                    "id[<]" =>$articleId
                ),
                "ORDER" => array(
                    "id" => "ASC"
                )
            );
            $preArticle = $articleModel->fetchRow($params,array("id"));
            if($preArticle){
                $articleChapter['pre'] = $preArticle['id'];
            }
            $this->_view->chapter = $articleChapter;

            $key = "";
            switch ($articleType['parent_id']){
                case ArticlesTypeModel::ARTICLE_TYPE_TANG :
                    $key = "suitangdetail";
                    break;
                case ArticlesTypeModel::ARTICLE_TYPE_SONG:
                    $key = "songyuandetail";
                    break;

            }

            $content = mb_substr(strip_tags($articleInfo['content']),0,95,'utf-8');
            $this->_view->seo = array(
                "title" => str_replace(array("{name}","{author_name}"),array($articleInfo['name'],$articleInfo['author']),$this->_seo[$key]['title']),
                "keywords" => str_replace(array("{name}","{author_name}"),array($articleInfo['name'],$articleInfo['author']),$this->_seo[$key]['keywords']),
                "description" => str_replace(array("{name}","{author_name}","{content}"),array($articleInfo['name'],$articleInfo['author'],$content),$this->_seo[$key]['description']),
            );

        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

    public function chapterAction(){
        try{
            $page = $this->get("page");
            $page = $page > 0 ? $page : 1;
            $offset = ($page-1)*50;
            $articleTypeId = $this->get("id");

            $articleModel = new ArticlesModel();
            $params = array(
                "class_type" => (int)$articleTypeId,
                "status" => 1
            );
            $order = array(
                "article_order"=>"ASC",
                "id" => "ASC"
            );
            $chaptersList = $articleModel->getList($params,0,false,$order);
            //var_dump($chaptersList);exit;
            $this->_view->list = $chaptersList['list'];

            $articleTypeModel = new ArticlesTypeModel();
            $articleType = $articleTypeModel->find($articleTypeId);
            $this->_view->article_type = $articleType;

            $key = "";
            switch ($articleType['parent_id']){
                case ArticlesTypeModel::ARTICLE_TYPE_TANG :
                    $key = "suitangchapter";
                    break;
                case ArticlesTypeModel::ARTICLE_TYPE_SONG:
                    $key = "songyuanchapter";
                    break;

            }
            $description = $articleType['content']?$articleType['name']."简介及资料:".strip_tags($articleType['content']) :$this->_seo[$key]['description'];

            $this->_view->seo = array(
                "title" => str_replace("{name}",$articleType['name'],$this->_seo[$key]['title']),
                "keywords" => str_replace("{name}",$articleType['name'],$this->_seo[$key]['keywords']),
                "description" => mb_substr($description,0,95,'utf-8'),
            );

        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }

    }

}
