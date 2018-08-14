<?php
class CaptcheController extends Yaf_Controller_Abstract{
    public function indexAction(){
        $captche =  \YC\Captche::getCaptche();
        exit;    
    }


}
