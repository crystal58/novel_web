<?php 
namespace Service;
use UserModel;
class AuthService extends Service{
    const COOKIE_NAME = "U";
    const COOKIE_EXPIRE = 86400;
    public function login($data){
        $userInfo = $this->_checkUser($data);
        $cookie = $this->_writeCookie($userInfo);
        if(!$cookie){
            throw new Excetion("write cookie error");
        }
        return $userInfo;
    }
    private function _checkUser($params){
        $user = new UserModel();
        $userInfo = $user->getUserByName($params['name']);
        if(empty($userInfo) || $userInfo['pwd'] != md5($params['password'])){
            error_log($userInfo['pwd']);
            error_log(md5($params['password']));
            error_log($params['password']);
            throw new Exception("用户名或者密码错误");
        }
        return $userInfo;
    }
    private function _writeCookie($data){
        $id = isset($data['id']) ? $data['id'] : (isset($data['i'])?$data['i']:'');
        $name = isset($data['login_name']) ? $data['login_name'] : (isset($data['n'])?$data['n']:'');
        if(!$id || !$name){
            throw new Exception("write cookie error!!");
        }
        $c = array(
            "i" => $id,
            "n" => $name,
            "t" => time()
        );
        $str = array();
        foreach($c as $key=>$value){
            $str[] = $key."=".$value;
        }
        $str = join("&",$str);
        $sign = new \YC\Sign();
        $s = $sign->encryptData($str);
        $cookieStr = $str."&s=".$s;
        $ret = setrawcookie(self::COOKIE_NAME,$cookieStr,time()+self::COOKIE_EXPIRE,"/");
        
        return $ret; 
    }

    public function Auth(){
        $data = $this->_readCookie();
        if(!$data || empty($data['i']) || empty($data['n'])){
            return false;
        }
        $this->_writeCookie($data);
        return $data;   
    }

    private function _readCookie(){
        $cookieStr = isset($_COOKIE[self::COOKIE_NAME])?$_COOKIE[self::COOKIE_NAME]:"";
        if(empty($cookieStr)){
            return false;
        }
        $sign = new \YC\Sign();
        $verifyStr = explode("&s=",$cookieStr);
        $decodeStr = $sign->decryptData($verifyStr[1]);

        if(empty($verifyStr) || empty($decodeStr) || $verifyStr[0] != $decodeStr){
            return false;
        }
        $data = array();
        $cookie = explode("&",$verifyStr[0]);
        foreach($cookie as $value){
            $tmp = explode("=",$value);
            $data[$tmp[0]] = $tmp[1];
        }
        return $data;
    }

    public function clearAuthCookie(){
        $cookieStr = isset($_COOKIE[self::COOKIE_NAME])?$_COOKIE[self::COOKIE_NAME]:"";
        if($cookieStr){
            setrawcookie(self::COOKIE_NAME,$cookieStr,time()-self::COOKIE_EXPIRE,"/");
        }
    }
}
