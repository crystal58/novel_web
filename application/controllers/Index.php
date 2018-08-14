<?php
/**
 * @name IndexController
 * @author crystal
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class IndexController extends AbstractController {

	/** 
     * 默认动作
     * Yaf支持直接把Yaf_Request_Abstract::getParam()得到的同名参数作为Action的形参
     * 对于如下的例子, 当访问http://yourhost/yafweb/index/index/index/name/crystal 的时候, 你就会发现不同
     */
    public function indexAction() {


    }
    public function infoAction(){
        
		$this->getView()->assign("message","Hello , Welcome ".$this->_operatorName);
    }
}
