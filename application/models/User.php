<?php
class UserModel extends AbstractModel {
    protected $_table = "user";
    protected $_database = "account";
    protected $_primary = "id"; 

    public function getUserByName($name){
        $where = array(
            "AND" => array(
                "login_name" => $name,
                "status" => 1
            )
        );

        return $this->fetchRow($where);
    }   
    /**
     * isCount   是否返回count
     */
    public function getList($params,$offset = 0,$pageSize = null,$isCount = false) {
        if(empty($params) && empty($pageSize)){
            return array();   
        }
        $where = array();
        foreach($params as $key=>$value){
            $where['AND'] = array(
                $key => $value
            );
        }
        
        $result = array();
        if($isCount){
            $result['cnt'] = $this->count($where);
        }
        if($pageSize){
            $where['LIMIT'] = array($offset,$pageSize);
        }
        $where['ORDER'] = array(
            "id"=>"DESC"
        );
        $result['list'] = $this->fetchAll($where);
        return $result;
    }

}
