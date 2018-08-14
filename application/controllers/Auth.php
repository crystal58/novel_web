<?php 
use Service\AuthService;
/**
 * @name AuthController
 * @author crystal
 * @desc 认证控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class AuthController extends AbstractController {
    /**
     * 默认动作
     * Yaf支持直接把Yaf_Request_Abstract::getParam()得到的同名参数作为Action的形参
     * 对于如下的例子, 当访问http://yourhost/yafweb/index/index/index/name/crystal 的时候, 你就会发现不同
     */
    public function loginAction() {
        //1. fetch query
        $done = $this->get("done", "");
        $logout = $this->get("logout",0);
        try{
                if($this->getRequest()->isPost()){
                    $name = $this->getPost('name');
                    $pwd = $this->getPost('password');
                    $captche = $this->getPost('code');
                    if(empty($name)){
                        throw new Exception("用户名不能为空");
                    }
                    if(empty($pwd)){
                        throw new Exception("密码不能为空");
                    }
                    if(empty($captche)){
                        throw new Exception("验证码不能为空");
                    }
                    $session = Yaf_Session::getInstance();
                    $captcheSession = $session->get("captche_session");
                    if(strtolower($captche) != strtolower($captcheSession)){
                        error_log($captche."__".$captcheSession);
                            throw new Exception("验证码错误");
                    }
                    $auth = new AuthService();

                    $userInfo = $auth->login(array("name" => $name,"password" => $pwd));
                    if($done){
                        $this->redirect($done);
                    }
                    $this->redirect("/index/");
                }
                $result = array(
                    "code" => 200
                );
        }catch(Exception $e){
           error_log($e);
           $result = array(
                "code" => $e->getCode(),
                "msg" => $e->getMessage()
           );
           $this->getView()->assign("exception",$e);
           $this->getView()->display("error/error.phtml");
           exit;
        }
        //2. fetch model
        //$model = new SampleModel();
        //3. assign
        //$this->getView()->assign("content", $model->selectSample());
        //$this->getView()->assign("name", $name);

        //4. render by Yaf, 如果这里返回FALSE, Yaf将不会调用自动视图引擎Render模板
        return $result;
    }

    public function logoutAction(){
        try{
            $auth = new AuthService();
            $auth->clearAuthCookie();
        }catch(Exception $e){
            error_log($e);
        }
        $this->redirect("/Auth/login");
    }
    
}
