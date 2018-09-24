<?php
/**
 * Created by PhpStorm.
 * User: zhangyapin
 * Date: 2018/9/19
 * Time: 下午3:04
 */
class AuthorsController extends AbstractController {
    public function listAction(){
        try{
            $id = $this->get("id");
            $authorModel = new AuthorModel();
            $params = array(
                "status" => AuthorModel::AUTHOR_STATUS
            );
            if($id > 0){
                $params['novel_class_id'] = $id;
            }

            $authorList = $authorModel->getList($params);
            $this->_view->author_list = $authorList['list'];
            $authors = "";
            foreach ($authorList['list'] as $value){
                $authors .= $value['author_name'];
            }
            $this->_view->seo = array(
                "title" => isset(NovelModel::$_novel_class_type[$id])?str_replace("{novelclass}",NovelModel::$_novel_class_type[$id],$this->_seo['authorlist']['title']):"",
                "keywords" => isset(NovelModel::$_novel_class_type[$id])?str_replace("{novelclass}",NovelModel::$_novel_class_type[$id],$this->_seo['authorlist']['keywords']):"",
                "description" => isset(NovelModel::$_novel_class_type[$id])?str_replace(array("{novelclass}","{authors}"),array(NovelModel::$_novel_class_type[$id],$authors),$this->_seo['authorlist']['description']):"",
            );
        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }

    }
}