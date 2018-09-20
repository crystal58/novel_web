<?php
use Service\AuthService;
/**
 * @name IndexController
 * @author crystal
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class AbstractController extends Yaf_Controller_Abstract {
    protected $_operatorName;
    protected $_operatorId;
    protected $_json = false;

    public function getParam($key,$default = ""){
        return $this->getRequest()->getQuery( $key , $default );
    }
    public function getPost($key,$default = ""){
        $h = new \Yaf_Request_Http();
        return $h->getPost($key,$default);
    }
    public function get($key,$default=""){
        $h = new \Yaf_Request_Http();
        return $h->get($key,$default);
    }
    public function init(){
        $config = Yaf_Registry::get("dbconfig");
        $this->_view->web_url = $config['web_url'];


//        $controllerName = $this->getRequest()->getControllerName();
//        $auth = new AuthService();
//        $data = $auth->Auth();
//        $controllerName = strtolower($controllerName);
//        if(empty($data) && $controllerName != "auth"){
//            $this->redirect("/auth/login");
//            exit;
//        }
//        $this->_operatorId = $data['i'];
//        $this->_operatorName = $data['n'];

    }
    protected function processException($class, $method, $e) {
        \YC\LoggerHelper::ERR('ACCESS_' . strtolower($class) . "_" . strtolower($method), $e->__toString());
        $result = array(
            "code" => $e->getCode(),
            "msg" => $e->getMessage()
        );
        if($this->_json){
            return $result;
        }
        $this->_view->message = "出错了";
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $this->getView()->display($this->getView()->getScriptPath() . "/error/error.phtml");
    }

    protected function renderJson(array $parameters = null) {
        header("Content-Type: application/json; charset=utf8");
        echo json_encode($parameters, JSON_UNESCAPED_UNICODE);
        exit;
    }


}
