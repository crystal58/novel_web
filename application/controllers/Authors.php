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
            $authorModel = new AuthorModel();
            $params = array(
                "status" => AuthorModel::AUTHOR_STATUS

            );

            $authorList = $authorModel->getList($params);
            $this->_view->author_list = $authorList['list'];
        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }

    }
}