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
    protected $_seo = array();
    protected $_active = "index";
    protected $_webUrl = "";

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
        $this->_webUrl = $config['web_url'];
        $novelClass = NovelModel::$_novel_class_type;
        $this->_view->novel_class = $novelClass;
        $novelClassPinXie = NovelModel::$_novel_class_pinxie;
        $this->_view->pinxie = $novelClassPinXie;
        $iniConfig = Yaf_Registry::get("config");
        $this->_seo = $iniConfig['seo'];
        $this->_view->active = $this->_active;




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
        $this->_view->message = "出错了！！！";
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $this->getView()->display($this->getView()->getScriptPath() . "/error/error.phtml");
    }

    protected function renderJson(array $parameters = null) {
        header("Content-Type: application/json; charset=utf8");
        echo json_encode($parameters, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function filterWord($content){
        $config = Yaf_Registry::get("dbconfig");
        $word = $config['filter'];
        $content = preg_replace('/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i', '', $content);
        $content = preg_replace("/<a[^>]*>(.*?)<\/a*>/is", "", $content);
        foreach ($word as $value){
            $content = str_replace($value,"<a href='https://www.eeeaaa.cn'>文学星空</a>",$content);
        }
        return $content;
    }


}
