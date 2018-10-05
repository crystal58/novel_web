<?php
class keyValuesModel extends AbstractModel {

    protected $_table = "keyvalues";
    protected $_database = "account";
    protected $_primary = "id"; 


    public function getData($params) {
        if(empty($params)){
            return array();
        }
        $result= $this->fetchRow($params);
        return $result;
    }

}
