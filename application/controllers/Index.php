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
//            $authorModel = new AuthorModel();
//            $params = array('status' => 1);
//            $authorList = $authorModel->getList($params);
//            $this->_view->author_list = $authorList['list'];

            $keyValueModel = new keyValuesModel();
            $wuxiaData = $keyValueModel->getData(array("keys"=>"recommend_wuxia"));
            $yanqingData = $keyValueModel->getData(array("keys"=>"recommend_yanqing"));
            $schoolData = $keyValueModel->getData(array("keys"=>"recommend_xiaoyuan"));
            $xuanhuanData = $keyValueModel->getData(array("keys"=>"recommend_xuanhuan"));
            $wangluoData = $keyValueModel->getData(array("keys"=>"recommend_wangluo"));

            //$a = json_decode($wuxiaData['value'],true);
            $this->_view->wuxia_list = $wuxiaData ? json_decode($wuxiaData['value'],true) : array();
            $this->_view->yanqing_list = $yanqingData ? json_decode($yanqingData['value'],true) : array();
            $this->_view->xiaoyuan_list = $schoolData ? json_decode($schoolData['value'],true) :array();
            $this->_view->xuanhuan_list = $xuanhuanData ? json_decode($xuanhuanData['value'],true) :array();
            $this->_view->wangluo_list = $xuanhuanData ? json_decode($wangluoData['value'],true) :array();

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
