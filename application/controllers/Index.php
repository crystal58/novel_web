<?php
/**
 * @name IndexController
 * @author crystal
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class IndexController extends AbstractController {

	/** 
     * 首页
     */
    public function indexAction() {
        try{
            $authorModel = new AuthorModel();
            $params = array('status' => 1);
            $authorList = $authorModel->getList($params);
            $this->_view->author_list = $authorList['list'];

            $novelModel = new NovelModel();
            $searchParam = array(
                "AND" => array(
                    'status' => 1,
                )
            );
            $result = $novelModel->novelList($searchParam,0,12);
            $this->_view->novel_list = $result['list'];
            $this->_view->seo = array(
                "title" => $this->_seo['index']['title'],
                "keywords" => $this->_seo['index']['keywords'],
                "description" => $this->_seo['index']['description']
            );



        }catch (Exception $e) {
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }

    }

}
