<?php


class ArticleController extends AbstractController{
    const PAGESIZE = 48;

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->_active = "tang";
        $this->_view->active = $this->_active;
    }


    /**
     * 专题、作者列表
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
            $articleAuthor = $list = array();
            $typeCount = count($articleType['list']);

            $authorParams = array(
                "status" => ArticleAuthorModel::AUTHOR_STATUS,
                "class_type_id" => $classTypeId
            );
            $articleOffset = $articlePageSize = 0;
//            if($typeCount < self::PAGESIZE){
//                $articleOffset = 0;
//                $articlePageSize = self::PAGESIZE - $typeCount;
//
//            }else{
                if(($typeCount/self::PAGESIZE) < $page){
                    $articleOffset = ceil($typeCount/self::PAGESIZE) == $page ? 0 :$offset  - $typeCount;
                    $articlePageSize = ceil($typeCount/self::PAGESIZE) == $page ? self::PAGESIZE-($typeCount%self::PAGESIZE) : self::PAGESIZE;
                }
            //}
            $articleAuthorModel = new ArticleAuthorModel();
            if($articlePageSize){
                $articleAuthor = $articleAuthorModel->getList($authorParams,$articleOffset,$articlePageSize,true);
            }else{
                $articleAuthor['cnt'] = $articleAuthorModel->count(array("AND" => $authorParams));
            }
            $articleAuthorCount = empty($articleAuthor['cnt']) ? 0 : $articleAuthor['cnt'];
            $authorList = empty($articleAuthor['list'])?array():$articleAuthor['list'];
           // echo json_encode($articleAuthor);exit;
            $this->_view->author_list = $authorList;

            switch ($classTypeId){
                case ArticlesTypeModel::ARTICLE_TYPE_TANG :
                    $tabName = "唐诗";
                    $tabNameP = "tangshi";
                    if(count($articleType['list']) > 0 || count($articleAuthor['list']) >0){
                        $list[] = array(
                            "tab_name" => $tabName,
                            "path" => "tangshi",
                            "author_url_type" => "gushi",
                            "article_type" => array_slice($articleType['list'],$offset,self::PAGESIZE),
                            "author_list" => $articleAuthor['list']
                        );
                    }
                    break;
                case ArticlesTypeModel::ARTICLE_TYPE_SONG:
                    $tabName = "宋词";
                    $tabNameP = "ciqu";
                    if(count($articleType['list']) > 0 || count($articleAuthor['list']) >0){
                        $list[] = array(
                            "tab_name" => $tabName,
                            "article_type" => array_slice($articleType['list'],$offset,self::PAGESIZE),
                            "author_list" => $articleAuthor['list'],
                            "path" => "ciqu",
                            "author_url_type" => "songci"
                        );
                    }
                    break;
                case ArticlesTypeModel::ARTICLE_TYPE_YUAN:
                    $tabName = "元曲";
                    $tabNameP = "ciqu";
                    $yuanParams = array(
                        "status" => ArticlesTypeModel::ARTICLE_CLASS_STATUS,
                        "parent_id" => ArticlesTypeModel::ARTICLE_TYPE_YUAN
                    );
                    $yuanArticleType = $articleTypeModel->getList($yuanParams);

                    $authorParams = array(
                        "status" => ArticleAuthorModel::AUTHOR_STATUS,
                        "class_type_id" => ArticlesTypeModel::ARTICLE_TYPE_YUAN
                    );
                    $yuanArticleAuthor = $articleAuthorModel->getList($authorParams);
                    if(count($yuanArticleType['list']) > 0 || count($yuanArticleAuthor['list']) >0){
                        $list[] = array(
                            "tab_name" => $tabName,
                            "article_type" => $yuanArticleType['list'],
                            "author_list" => $yuanArticleAuthor['list'],
                            "path" => "ciqu",
                            "author_url_type" => "yuanqu"
                        );
                    }
                    break;

            }
            // $this->_view->url_type = $urlType?:"tangshi";
            $this->_view->page_num = ceil(($typeCount + $articleAuthorCount)/self::PAGESIZE);
            $this->_view->page_url = $this->_webUrl."/".$tabNameP."/list_".$classTypeId."_{page}.html";
            $this->_view->cur_page = $page;

//            $this->_view->url_type = $urlType?:"tangshi";
//            $this->_view->chapter_url_type = $chapterUrlType?:"gushi";
            $this->_view->seo = array(
                "title" => str_replace("{class}",$tabName,$this->_seo['gushi']['title']),
                "keywords" => str_replace("{class}",$tabName,$this->_seo['gushi']['keywords']),
                "description" => str_replace("{class}",$tabName,$this->_seo['gushi']['description']),
            );
            $this->_view->list = $list;



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
            //var_dump($articleInfo);exit;


            if(empty($articleInfo)){
                throw new Exception("章节不存在!",404);
            }

            $this->_view->article_info = $articleInfo;
            $classId = $articleInfo['class_type'];
            $articleTypeModel = new ArticlesTypeModel();
            $articleType = $articleTypeModel->find($classId);

            $this->_view->article_type = $articleType;
            $this->_view->article_type_id = $classId;

            $params = array(
                "status" => ArticlesModel::ARTICLE_CLASS_STATUS,
                "id[>]" => $articleInfo['id'],
                "class_type" => $articleInfo['class_type'],
                "is_part" => 0
            );
            $relateArticle = $articleModel->getList($params,0,10);
            if(count($relateArticle['list']) < 10){
                $params = array(
                    "status" => ArticlesModel::ARTICLE_CLASS_STATUS,
                    "id[<]" => $articleInfo['id'],
                    "class_type" => $articleInfo['class_type'],
                    "is_part" => 0
                );
                $relate = $articleModel->getList($params,0,10,array("id" => "DESC"));
                $relateArticle['list'] = array_merge($relateArticle['list'],$relate['list']);
            }
            $this->_view->relate_article = $relateArticle['list'];
            $paramsExtra = array();
            if($articleType['parent_id'] != 0 && $articleInfo['author_id'] > 0){
                $paramsExtra = array(
                    "author_id" => $articleInfo['author_id']
                );
            }
            $articleChapter['next'] = $articleChapter['pre'] = false;
            $params = array(
                "AND" => array(
                    "id[>]" =>$articleId,
                    "class_type" => $articleInfo['class_type']
                ),
                "ORDER" => array(
                    "id" => "ASC"
                )
            );
            $nextArticle = $articleModel->fetchRow(array_merge($params,$paramsExtra),array("id"));
            if($nextArticle){
                $articleChapter['next'] = $nextArticle['id'];
            }
            $params = array(
                "AND" => array(
                    "id[<]" =>$articleId,
                    "class_type" => $articleInfo['class_type']
                ),
                "ORDER" => array(
                    "id" => "DESC"
                )
            );
            $preArticle = $articleModel->fetchRow(array_merge($params,$paramsExtra),array("id"));
            if($preArticle){
                $articleChapter['pre'] = $preArticle['id'];
            }
            $this->_view->chapter = $articleChapter;

            $classType = $articleType['parent_id'] == 0 ? $articleType['id']:$articleType['parent_id'];
            switch ($classType){
                case ArticlesTypeModel::ARTICLE_TYPE_TANG :
                    $urlType = "tangshi";
                    $chapterUrlType = "gushi";
                    break;
                case ArticlesTypeModel::ARTICLE_TYPE_SONG:
                    $urlType = "ciqu";
                    $chapterUrlType = "songci";
                    break;
                case ArticlesTypeModel::ARTICLE_TYPE_YUAN:
                    $urlType = "ciqu";
                    $chapterUrlType = "yuanqu";
                    break;
                case ArticlesTypeModel::ARTICLE_TYPE_WENYANWEN:
                    $urlType = "wenyanwen";
                    break;

            }
            $this->_view->class_type = $classType;
            $this->_view->url_type = $urlType?:"tangshi";
            $this->_view->chapter_url_type = $chapterUrlType?:"gushi"; //作者作品列表

            $content = mb_substr(strip_tags($articleInfo['content']),0,95,'utf-8');
            $this->_view->seo = array(
                "title" => str_replace(array("{name}","{author_name}"),array(trim($articleInfo['name']),trim($articleInfo['author'])),$this->_seo['guishidetail']['title']),
                "keywords" => str_replace(array("{name}","{author_name}"),array(trim($articleInfo['name']),trim($articleInfo['author'])),$this->_seo['guishidetail']['keywords']),
                "description" => str_replace(array("{name}","{author_name}","{content}"),array(trim($articleInfo['name']),trim($articleInfo['author']),$content),$this->_seo['guishidetail']['description']),
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
            $chaptersList = $articleModel->getList($params,$offset,self::PAGESIZE,$order,true);
            //$chaptersList = $articleModel->getList($params);
            //var_dump($chaptersList);exit;
            $this->_view->list = $chaptersList['list'];

            $articleTypeModel = new ArticlesTypeModel();
            $articleType = $articleTypeModel->find($articleTypeId);
            $this->_view->article_type = $articleType;

            $key = "";
            switch ($articleType['parent_id']){
                case ArticlesTypeModel::ARTICLE_TYPE_TANG :
                    $key = "suitangchapter";
                    $urlType = "tangshi";
                    break;
                case ArticlesTypeModel::ARTICLE_TYPE_YUAN:
                case ArticlesTypeModel::ARTICLE_TYPE_SONG:
                    $key = "songyuanchapter";
                    $urlType = "ciqu";
                    break;
            }
            $chapterType = "chapter";
            if($articleTypeId == ArticlesTypeModel::ARTICLE_TYPE_WENYANWEN){
                $urlType = "wenyanwen";
                $chapterType = "list";
            }
            $this->_view->url_type = $urlType?:"tangshi";
            $this->_view->page_num = ceil($chaptersList['cnt']/self::PAGESIZE);
            $this->_view->page_url = $this->_webUrl."/".$this->_view->url_type."/".$chapterType."_".$articleTypeId."_{page}.html";
            $this->_view->cur_page = $page;

            $this->_view->seo = array(
                "title" => str_replace("{name}",trim($articleType['name']),$this->_seo['gushichapter']['title']),
                "keywords" => str_replace("{name}",trim($articleType['name']),$this->_seo['gushichapter']['keywords']),
                "description" => str_replace("{name}",trim($articleType['name']),$this->_seo['gushichapter']['description']).mb_substr(strip_tags($articleType['content']),0,80,'utf-8'),
            );


        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }

    }

    public function articlelistAction(){
        try{
            $page = $this->get("page");
            $page = $page > 0 ? $page : 1;
            $offset = ($page-1)*self::PAGESIZE;
            $authorId = $this->get("id");

            $articleModel = new ArticlesModel();
            $params = array(
                "author_id" => (int)$authorId,
                "status" => 1
            );
            $order = array(
                "article_order"=>"ASC",
                "id" => "ASC"
            );
            $chaptersList = $articleModel->getList($params,$offset,self::PAGESIZE,$order,true);
            //$chaptersList = $articleModel->getList($params);
            //var_dump($chaptersList);exit;
            $this->_view->list = $chaptersList['list'];

            $authorModel = new ArticleAuthorModel();
            $authorInfo = $authorModel->find($authorId);
            $this->_view->author_info = $authorInfo;

            switch ($authorInfo['class_type_id']){
                case ArticlesTypeModel::ARTICLE_TYPE_TANG :
                    $urlType = "tangshi";
                    $chapterUrlType = "gushi";
                    break;
                case ArticlesTypeModel::ARTICLE_TYPE_SONG:
                    $urlType = "ciqu";
                    $chapterUrlType = "songci";
                    break;
                case ArticlesTypeModel::ARTICLE_TYPE_YUAN:
                    $urlType = "ciqu";
                    $chapterUrlType = "yuanqu";
                    break;
            }
            $this->_view->url_type =$urlType?:"tangshi";
            $this->_view->page_num = ceil($chaptersList['cnt']/self::PAGESIZE);
            $this->_view->page_url = $this->_webUrl."/".$this->_view->url_type."/".$chapterUrlType."_".$authorId."_{page}.html";
            $this->_view->cur_page = $page;
           // $description = $authorInfo['description']?$authorInfo['author_name']."简介及资料:".strip_tags($authorInfo['description']) :$this->_seo[$key]['description'];

            $this->_view->seo = array(
                "title" => str_replace("{name}",$authorInfo['author_name'],$this->_seo['gushichapter']['title']),
                "keywords" => str_replace("{name}",$authorInfo['author_name'],$this->_seo['gushichapter']['keywords']),
                "description" => str_replace("{name}",$authorInfo['author_name'],$this->_seo['gushichapter']['description']).mb_substr(strip_tags($authorInfo['description']),0,80,'utf-8'),
            );

        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }

    }

}
